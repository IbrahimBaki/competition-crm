<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

use App\Models\User;
use App\Support\Models\GeneratesUuid;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    use GeneratesUuid;

    protected $table = 'report_schedules';

    protected $fillable = [
        'uuid',
        'name',
        'report_key',
        'format',
        'filters',
        'frequency',
        'day_of_week',
        'day_of_month',
        'run_at_time',
        'timezone',
        'recipients',
        'is_active',
        'created_by_user_id',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'immutable_datetime',
        'next_run_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Next fire time for a schedule shape, in the schedule's own timezone.
     * Shared by the sweep command (advancing after a run) and by schedule
     * creation (seeding the first run) so both agree on the cadence.
     */
    public static function nextRunFor(string $frequency, string $runAtTime, string $timezone): CarbonImmutable
    {
        $tz = new DateTimeZone($timezone);
        $time = CarbonImmutable::createFromFormat(
            substr_count($runAtTime, ':') === 2 ? 'H:i:s' : 'H:i',
            $runAtTime,
            $tz
        );

        $next = CarbonImmutable::now($tz);

        return match ($frequency) {
            'daily' => $next->addDay()->setTime($time->hour, $time->minute),
            'weekly' => $next->addWeek()->setTime($time->hour, $time->minute),
            'monthly' => $next->addMonth()->setTime($time->hour, $time->minute),
            default => $next,
        };
    }

    public function computeNextRun(): CarbonImmutable
    {
        return self::nextRunFor($this->frequency, $this->run_at_time, $this->timezone);
    }
}
