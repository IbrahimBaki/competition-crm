import { useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useReplaceBranchWorkingHours } from '../../api/wire';
import { WorkingWeekPreview } from './WorkingWeekPreview';
import type { BranchWorkingHour, BranchHoliday } from '../../types';

const DAY_LABEL_KEYS = [
  'admin.organisation.calendar.day.0',
  'admin.organisation.calendar.day.1',
  'admin.organisation.calendar.day.2',
  'admin.organisation.calendar.day.3',
  'admin.organisation.calendar.day.4',
  'admin.organisation.calendar.day.5',
  'admin.organisation.calendar.day.6',
];

function defaultWeek(): BranchWorkingHour[] {
  return Array.from({ length: 7 }, (_, dayOfWeek) => ({
    dayOfWeek,
    isWorking: dayOfWeek >= 1 && dayOfWeek <= 5,
    opensAt: dayOfWeek >= 1 && dayOfWeek <= 5 ? '09:00' : null,
    closesAt: dayOfWeek >= 1 && dayOfWeek <= 5 ? '17:00' : null,
  }));
}

interface BranchWorkingHoursEditorProps {
  branchId: string;
  initialDays: BranchWorkingHour[];
  holidays: BranchHoliday[];
}

// Days left disabled ARE the weekend pattern — there is no separate weekend
// field on ReplaceBranchWorkingHoursRequest. Submits are always the full
// seven-day week (replace semantics), never a partial diff.
export function BranchWorkingHoursEditor({ branchId, initialDays, holidays }: BranchWorkingHoursEditorProps) {
  const { t } = useTranslation();
  const [days, setDays] = useState<BranchWorkingHour[]>(
    initialDays.length === 7 ? initialDays : defaultWeek()
  );
  const [error, setError] = useState<string | undefined>();

  const mutation = useReplaceBranchWorkingHours({
    onError: () => setError(t('admin.organisation.calendar.save_error')),
  });

  const fieldError = days.find(
    (day) => day.isWorking && day.opensAt && day.closesAt && day.opensAt >= day.closesAt
  );
  const missingTimes = days.some((day) => day.isWorking && (!day.opensAt || !day.closesAt));
  const canSubmit = !fieldError && !missingTimes;

  const updateDay = (dayOfWeek: number, patch: Partial<BranchWorkingHour>) => {
    setDays((current) =>
      current.map((day) => (day.dayOfWeek === dayOfWeek ? { ...day, ...patch } : day))
    );
  };

  const toggleWorking = (dayOfWeek: number, isWorking: boolean) => {
    updateDay(dayOfWeek, {
      isWorking,
      opensAt: isWorking ? '09:00' : null,
      closesAt: isWorking ? '17:00' : null,
    });
  };

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    setError(undefined);
    if (!canSubmit) return;
    mutation.mutate({ branch: branchId, days });
  };

  return (
    <div className="grid gap-4 md:grid-cols-2">
      <form onSubmit={handleSubmit} className="space-y-2">
        {days.map((day) => (
          <div key={day.dayOfWeek} className="flex items-center gap-2">
            <label className="flex w-32 items-center gap-2 text-sm text-gray-700">
              <input
                type="checkbox"
                checked={day.isWorking}
                onChange={(event) => toggleWorking(day.dayOfWeek, event.target.checked)}
              />
              {t(DAY_LABEL_KEYS[day.dayOfWeek] ?? 'admin.organisation.calendar.day.0')}
            </label>
            <input
              type="time"
              value={day.opensAt ?? ''}
              disabled={!day.isWorking}
              onChange={(event) => updateDay(day.dayOfWeek, { opensAt: event.target.value })}
              className="rounded border border-gray-300 px-2 py-1 text-sm disabled:bg-gray-100"
            />
            <span className="text-gray-400">–</span>
            <input
              type="time"
              value={day.closesAt ?? ''}
              disabled={!day.isWorking}
              onChange={(event) => updateDay(day.dayOfWeek, { closesAt: event.target.value })}
              className="rounded border border-gray-300 px-2 py-1 text-sm disabled:bg-gray-100"
            />
          </div>
        ))}

        {fieldError && (
          <p className="text-sm text-red-600">{t('admin.organisation.calendar.open_after_close_error')}</p>
        )}
        {!fieldError && missingTimes && (
          <p className="text-sm text-red-600">{t('admin.organisation.calendar.missing_times_error')}</p>
        )}
        {error && <p className="text-sm text-red-600">{error}</p>}

        <button
          type="submit"
          disabled={!canSubmit || mutation.isPending}
          className="rounded bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
        >
          {t('admin.organisation.actions.save')}
        </button>
      </form>

      <WorkingWeekPreview days={days} holidays={holidays} />
    </div>
  );
}
