import { useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { ConfirmActionDialog } from '@/shared/confirm/ConfirmActionDialog';
import { normaliseApiError } from '@/api/http/errors';
import { adminErrorMessageKey } from '../../api/errorCodes';
import { useCreateBranchHoliday, useDeleteBranchHoliday } from '../../api/wire';
import type { BranchHoliday } from '../../types';

interface BranchHolidaysPanelProps {
  branchId: string;
  holidays: BranchHoliday[];
}

type RecurrenceMode = 'date' | 'recurring';

export function BranchHolidaysPanel({ branchId, holidays }: BranchHolidaysPanelProps) {
  const { t, i18n } = useTranslation();
  const activeLocale = i18n.language.startsWith('ar') ? 'ar' : 'en';

  const [nameAr, setNameAr] = useState('');
  const [nameEn, setNameEn] = useState('');
  const [mode, setMode] = useState<RecurrenceMode>('date');
  const [date, setDate] = useState('');
  const [recurringMonthDay, setRecurringMonthDay] = useState('');
  const [deleteTarget, setDeleteTarget] = useState<BranchHoliday | null>(null);
  const [error, setError] = useState<string | undefined>();

  const createMutation = useCreateBranchHoliday({
    onSuccess: () => {
      setNameAr('');
      setNameEn('');
      setDate('');
      setRecurringMonthDay('');
    },
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const deleteMutation = useDeleteBranchHoliday({
    onSuccess: () => setDeleteTarget(null),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const canSubmit =
    nameAr.trim().length > 0 &&
    nameEn.trim().length > 0 &&
    (mode === 'date' ? date.length > 0 : recurringMonthDay.length > 0);

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    setError(undefined);
    createMutation.mutate({
      branch: branchId,
      name: { ar: nameAr.trim(), en: nameEn.trim() },
      date: mode === 'date' ? date : null,
      recurringMonthDay: mode === 'recurring' ? recurringMonthDay : null,
    });
  };

  return (
    <div className="space-y-4">
      <ul className="divide-y divide-gray-100 rounded border border-gray-200 bg-white">
        {holidays.length === 0 && (
          <li className="px-3 py-2 text-sm text-gray-500">{t('admin.organisation.calendar.no_holidays')}</li>
        )}
        {holidays.map((holiday) => (
          <li key={holiday.id} className="flex items-center justify-between px-3 py-2 text-sm">
            <span>
              {holiday.name} —{' '}
              {holiday.date ?? t('admin.organisation.calendar.recurring_on', { monthDay: holiday.recurringMonthDay })}
            </span>
            <button
              type="button"
              onClick={() => setDeleteTarget(holiday)}
              className="text-red-600 hover:underline"
            >
              {t('admin.organisation.actions.delete')}
            </button>
          </li>
        ))}
      </ul>

      <form onSubmit={handleSubmit} className="grid gap-2 rounded border border-gray-200 bg-white p-3 sm:grid-cols-2">
        <input
          value={activeLocale === 'ar' ? nameAr : nameEn}
          onChange={(event) =>
            activeLocale === 'ar' ? setNameAr(event.target.value) : setNameEn(event.target.value)
          }
          placeholder={t('admin.organisation.calendar.holiday_name_active_locale_placeholder') ?? undefined}
          className="rounded border border-gray-300 px-2 py-1.5 text-sm"
        />
        <input
          value={activeLocale === 'ar' ? nameEn : nameAr}
          onChange={(event) =>
            activeLocale === 'ar' ? setNameEn(event.target.value) : setNameAr(event.target.value)
          }
          placeholder={t('admin.organisation.calendar.holiday_name_other_locale_placeholder') ?? undefined}
          className="rounded border border-gray-300 px-2 py-1.5 text-sm"
        />

        <div className="flex items-center gap-3 text-sm sm:col-span-2">
          <label className="flex items-center gap-1">
            <input
              type="radio"
              name="holiday-mode"
              checked={mode === 'date'}
              onChange={() => setMode('date')}
            />
            {t('admin.organisation.calendar.holiday_mode_date')}
          </label>
          <label className="flex items-center gap-1">
            <input
              type="radio"
              name="holiday-mode"
              checked={mode === 'recurring'}
              onChange={() => setMode('recurring')}
            />
            {t('admin.organisation.calendar.holiday_mode_recurring')}
          </label>
        </div>

        {mode === 'date' ? (
          <input
            type="date"
            value={date}
            onChange={(event) => setDate(event.target.value)}
            className="rounded border border-gray-300 px-2 py-1.5 text-sm"
          />
        ) : (
          <input
            type="text"
            value={recurringMonthDay}
            onChange={(event) => setRecurringMonthDay(event.target.value)}
            pattern="(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])"
            placeholder={t('admin.organisation.calendar.recurring_month_day_placeholder') ?? undefined}
            className="rounded border border-gray-300 px-2 py-1.5 text-sm"
          />
        )}

        <div className="sm:col-span-2">
          <button
            type="submit"
            disabled={!canSubmit || createMutation.isPending}
            className="rounded bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {t('admin.organisation.calendar.add_holiday')}
          </button>
        </div>

        {error && <p className="text-sm text-red-600 sm:col-span-2">{error}</p>}
      </form>

      {deleteTarget && (
        <ConfirmActionDialog
          titleKey="admin.organisation.calendar.delete_holiday_dialog_title"
          consequenceKey="admin.organisation.calendar.delete_holiday_consequence"
          confirmLabelKey="admin.organisation.actions.delete"
          onConfirm={() => deleteMutation.mutate({ branch: branchId, holiday: deleteTarget.id })}
          onCancel={() => setDeleteTarget(null)}
          isSubmitting={deleteMutation.isPending}
          error={error}
          destructive
        />
      )}
    </div>
  );
}
