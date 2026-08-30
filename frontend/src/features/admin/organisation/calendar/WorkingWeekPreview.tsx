import { useTranslation } from 'react-i18next';
import type { BranchWorkingHour, BranchHoliday } from '../../types';

interface WorkingWeekPreviewProps {
  days: BranchWorkingHour[];
  holidays: BranchHoliday[];
}

const DAY_LABEL_KEYS = [
  'admin.organisation.calendar.day.0',
  'admin.organisation.calendar.day.1',
  'admin.organisation.calendar.day.2',
  'admin.organisation.calendar.day.3',
  'admin.organisation.calendar.day.4',
  'admin.organisation.calendar.day.5',
  'admin.organisation.calendar.day.6',
];

function minutesBetween(opensAt: string, closesAt: string): number {
  const [openHour = 0, openMinute = 0] = opensAt.split(':').map(Number);
  const [closeHour = 0, closeMinute = 0] = closesAt.split(':').map(Number);
  return closeHour * 60 + closeMinute - (openHour * 60 + openMinute);
}

function isWithinNext30Days(monthDay: string | null, isoDate: string | null): boolean {
  const today = new Date();
  const in30Days = new Date(today);
  in30Days.setDate(in30Days.getDate() + 30);

  if (isoDate) {
    const date = new Date(isoDate);
    return date >= today && date <= in30Days;
  }

  if (monthDay) {
    const [month, day] = monthDay.split('-').map(Number);
    const candidate = new Date(today.getFullYear(), (month ?? 1) - 1, day ?? 1);
    if (candidate < today) candidate.setFullYear(candidate.getFullYear() + 1);
    return candidate <= in30Days;
  }

  return false;
}

// Renders purely from the form's current in-memory values — no endpoint
// call, no clock/SLA arithmetic beyond summing the entered ranges. See
// frontend/src/__tests__/no-sla-recalculation.test.ts (scoped to
// features/tickets, but this component follows the same discipline).
export function WorkingWeekPreview({ days, holidays }: WorkingWeekPreviewProps) {
  const { t } = useTranslation();

  const workingDays = days.filter((day) => day.isWorking && day.opensAt && day.closesAt);
  const totalMinutes = workingDays.reduce(
    (sum, day) => sum + minutesBetween(day.opensAt as string, day.closesAt as string),
    0
  );
  const totalHours = Math.round((totalMinutes / 60) * 10) / 10;

  const upcomingHolidays = holidays.filter((holiday) =>
    isWithinNext30Days(holiday.recurringMonthDay, holiday.date)
  );

  if (workingDays.length === 0) {
    return (
      <div className="rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
        {t('admin.organisation.calendar.no_working_hours')}
      </div>
    );
  }

  return (
    <div className="rounded border border-gray-200 bg-gray-50 p-4 text-sm">
      <p className="mb-2 font-medium text-gray-900">
        {t('admin.organisation.calendar.total_hours', { hours: totalHours })}
      </p>
      <ul className="mb-3 space-y-1">
        {days.map((day) => (
          <li key={day.dayOfWeek} className="flex justify-between text-gray-700">
            <span>{t(DAY_LABEL_KEYS[day.dayOfWeek] ?? 'admin.organisation.calendar.day.0')}</span>
            <span>
              {day.isWorking && day.opensAt && day.closesAt
                ? `${day.opensAt} – ${day.closesAt}`
                : t('admin.organisation.calendar.closed')}
            </span>
          </li>
        ))}
      </ul>
      {upcomingHolidays.length > 0 && (
        <div>
          <p className="mb-1 font-medium text-gray-900">
            {t('admin.organisation.calendar.upcoming_holidays')}
          </p>
          <ul className="space-y-0.5 text-gray-700">
            {upcomingHolidays.map((holiday) => (
              <li key={holiday.id}>{holiday.name}</li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
