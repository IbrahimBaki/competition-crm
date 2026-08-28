<?php

namespace App\Domains\Organisation\Services;

use App\Domains\Organisation\Models\Branch;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * This is the single source of truth for business-time arithmetic.
 *
 * Do not duplicate this logic in the SLA engine, escalation jobs, or reports.
 * Do not invent alternative working-time calculations elsewhere.
 *
 * All results reflect the calendar as it exists at call time. Callers that
 * need immutable historical values (e.g. an SLA breach snapshot) must
 * persist the computed integer on their own row. This service does not
 * version calendars.
 */
class WorkingTimeService
{
    /**
     * Elapsed working minutes between $from and $to for the given branch.
     * Both instants are treated as UTC points in time.
     * Result is >= 0; if $to < $from, returns 0 (never negative).
     */
    public function elapsedWorkingMinutes(
        Branch $branch,
        DateTimeInterface $from,
        DateTimeInterface $to,
    ): int {
        if ($to < $from) {
            return 0;
        }

        if ($from == $to) {
            return 0;
        }

        // 24/7 fast path
        if ($branch->is_24_7) {
            $diff = $to->getTimestamp() - $from->getTimestamp();

            return (int) floor($diff / 60);
        }

        $tz = $branch->timezoneObject();
        $fromLocal = (new DateTimeImmutable('@'.$from->getTimestamp()))->setTimezone($tz);
        $toLocal = (new DateTimeImmutable('@'.$to->getTimestamp()))->setTimezone($tz);

        $holidays = $this->materialiseHolidays($branch, $fromLocal, $toLocal);
        $workingHours = $branch->workingHours()->get()->keyBy('day_of_week');

        $totalMinutes = 0;
        $currentLocal = $fromLocal->setTime(0, 0, 0);

        while ($currentLocal->format('Y-m-d') <= $toLocal->format('Y-m-d')) {
            $dayString = $currentLocal->format('Y-m-d');

            // Skip if holiday
            if (in_array($dayString, $holidays, true)) {
                $currentLocal = $currentLocal->add(new DateInterval('P1D'));

                continue;
            }

            $dayOfWeek = (int) $currentLocal->format('w');
            $dayHours = $workingHours->get($dayOfWeek);

            if (! $dayHours || ! $dayHours->is_working) {
                $currentLocal = $currentLocal->add(new DateInterval('P1D'));

                continue;
            }

            // Parse opening/closing times
            [$openHour, $openMin] = sscanf($dayHours->opens_at, '%d:%d');
            [$closeHour, $closeMin] = sscanf($dayHours->closes_at, '%d:%d');

            $dayOpen = $currentLocal->setTime($openHour, $openMin, 0);
            $dayClose = $currentLocal->setTime($closeHour, $closeMin, 0);

            // Compute intersection [max(dayOpen, fromLocal), min(dayClose, toLocal)]
            $windowStart = $fromLocal > $dayOpen ? $fromLocal : $dayOpen;
            $windowEnd = $toLocal < $dayClose ? $toLocal : $dayClose;

            if ($windowStart < $windowEnd) {
                $diff = $windowEnd->getTimestamp() - $windowStart->getTimestamp();
                $totalMinutes += (int) floor($diff / 60);
            }

            $currentLocal = $currentLocal->add(new DateInterval('P1D'));
        }

        return $totalMinutes;
    }

    /**
     * Instant reached by adding $minutes working minutes to $start for the
     * given branch. If $start falls outside working hours, the clock only
     * begins ticking at the next working minute. Returned instant is UTC.
     */
    public function addWorkingMinutes(
        Branch $branch,
        DateTimeInterface $start,
        int $minutes,
    ): DateTimeImmutable {
        // 24/7 fast path
        if ($branch->is_24_7) {
            return (new DateTimeImmutable('@'.$start->getTimestamp()))
                ->add(new DateInterval('PT'.$minutes.'M'));
        }

        $tz = $branch->timezoneObject();
        $currentLocal = (new DateTimeImmutable('@'.$start->getTimestamp()))->setTimezone($tz);

        $holidays = $this->materialiseHolidaysFromYear($branch, $currentLocal->format('Y'));
        $workingHours = $branch->workingHours()->get()->keyBy('day_of_week');

        $remaining = $minutes;

        while ($remaining > 0) {
            $dayString = $currentLocal->format('Y-m-d');

            // Skip if holiday
            if (in_array($dayString, $holidays, true)) {
                $currentLocal = $currentLocal->setTime(0, 0, 0)->add(new DateInterval('P1D'));

                continue;
            }

            $dayOfWeek = (int) $currentLocal->format('w');
            $dayHours = $workingHours->get($dayOfWeek);

            if (! $dayHours || ! $dayHours->is_working) {
                $currentLocal = $currentLocal->setTime(0, 0, 0)->add(new DateInterval('P1D'));

                continue;
            }

            // Parse opening/closing times
            [$openHour, $openMin] = sscanf($dayHours->opens_at, '%d:%d');
            [$closeHour, $closeMin] = sscanf($dayHours->closes_at, '%d:%d');

            $dayOpen = $currentLocal->setTime($openHour, $openMin, 0);
            $dayClose = $currentLocal->setTime($closeHour, $closeMin, 0);

            // If currently before day opens, jump to opening
            if ($currentLocal < $dayOpen) {
                $currentLocal = $dayOpen;
            }

            // If currently after day closes, move to next day
            if ($currentLocal >= $dayClose) {
                $currentLocal = $currentLocal->setTime(0, 0, 0)->add(new DateInterval('P1D'));

                continue;
            }

            // Consume minutes on this day
            $capacitySeconds = $dayClose->getTimestamp() - $currentLocal->getTimestamp();
            $consumeSeconds = min($remaining * 60, $capacitySeconds);
            $consumeMinutes = (int) floor($consumeSeconds / 60);

            $currentLocal = $currentLocal->add(new DateInterval('PT'.$consumeSeconds.'S'));
            $remaining -= $consumeMinutes;
        }

        // Convert back to UTC
        return $currentLocal->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * Materialise all holiday dates (one-off + recurring) between two local dates.
     *
     * @return array<int, string> Array of 'Y-m-d' date strings
     */
    private function materialiseHolidays(
        Branch $branch,
        DateTimeImmutable $fromLocal,
        DateTimeImmutable $toLocal,
    ): array {
        $holidays = [];

        // One-off dates
        foreach ($branch->holidays()->whereNotNull('date')->get() as $holiday) {
            $dateStr = $holiday->date->format('Y-m-d');
            if ($dateStr >= $fromLocal->format('Y-m-d') && $dateStr <= $toLocal->format('Y-m-d')) {
                $holidays[] = $dateStr;
            }
        }

        // Recurring dates: expand for each year in the span
        $fromYear = (int) $fromLocal->format('Y');
        $toYear = (int) $toLocal->format('Y');

        foreach ($branch->holidays()->whereNotNull('recurring_month_day')->get() as $holiday) {
            for ($year = $fromYear; $year <= $toYear; $year++) {
                $dateStr = $year.'-'.$holiday->recurring_month_day;
                // Validate that the date is real (skip 02-29 in non-leap years)
                try {
                    \DateTime::createFromFormat('Y-m-d', $dateStr);
                    if ($dateStr >= $fromLocal->format('Y-m-d') && $dateStr <= $toLocal->format('Y-m-d')) {
                        $holidays[] = $dateStr;
                    }
                } catch (\Throwable) {
                    // Skip invalid dates like 02-29 in non-leap years
                }
            }
        }

        return array_unique($holidays);
    }

    /**
     * Materialise all holiday dates for a single year.
     *
     * @return array<int, string> Array of 'Y-m-d' date strings
     */
    private function materialiseHolidaysFromYear(Branch $branch, string $year): array
    {
        $holidays = [];

        // One-off dates in this year
        foreach ($branch->holidays()->whereNotNull('date')->get() as $holiday) {
            if ($holiday->date->format('Y') == $year) {
                $holidays[] = $holiday->date->format('Y-m-d');
            }
        }

        // Recurring dates for this year
        foreach ($branch->holidays()->whereNotNull('recurring_month_day')->get() as $holiday) {
            $dateStr = $year.'-'.$holiday->recurring_month_day;
            try {
                \DateTime::createFromFormat('Y-m-d', $dateStr);
                $holidays[] = $dateStr;
            } catch (\Throwable) {
                // Skip invalid dates
            }
        }

        return array_unique($holidays);
    }
}
