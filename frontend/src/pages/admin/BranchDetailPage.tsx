import { useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import {
  useGetBranch,
  useGetBranchWorkingHours,
  useGetBranchHolidays,
} from '@/api/generated/organization/organization';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { BranchForm } from '@/features/admin/organisation/BranchForm';
import { RouteFormModal } from '@/shell/RouteFormModal';
import { BranchWorkingHoursEditor } from '@/features/admin/organisation/calendar/BranchWorkingHoursEditor';
import { BranchHolidaysPanel } from '@/features/admin/organisation/calendar/BranchHolidaysPanel';
import { toBranch, toBranchWorkingHour, toBranchHoliday } from '@/features/admin/api/wire';
import type { Branch, BranchWorkingHour, BranchHoliday } from '@/features/admin/types';

export function BranchDetailPage() {
  const { t } = useTranslation();
  const { branchId } = useParams<{ branchId: string }>();
  const isNew = branchId === 'new';
  const id = branchId ?? '';

  const branchQuery = useGetBranch(id, {
    query: { enabled: !isNew },
  }) as unknown as UseQueryResult<Record<string, unknown>, unknown>;
  const workingHoursQuery = useGetBranchWorkingHours(id, {
    query: { enabled: !isNew },
  }) as unknown as UseQueryResult<{ items: Record<string, unknown>[] } | Record<string, unknown>[], unknown>;
  const holidaysQuery = useGetBranchHolidays(id, undefined, {
    query: { enabled: !isNew },
  }) as unknown as UseQueryResult<{ items: Record<string, unknown>[] }, unknown>;

  if (isNew) {
    return <RouteFormModal title={t('admin.organisation.branch.create_title')} fallback="/admin/branches"><BranchForm /></RouteFormModal>;
  }

  return (
    <AsyncBoundary query={branchQuery}>
      {(raw) => {
        const branch: Branch = toBranch(raw);
        const rawHours = Array.isArray(workingHoursQuery.data)
          ? workingHoursQuery.data
          : (workingHoursQuery.data?.items ?? []);
        const days: BranchWorkingHour[] = rawHours.map(toBranchWorkingHour);
        const holidays: BranchHoliday[] = (holidaysQuery.data?.items ?? []).map(toBranchHoliday);

        return (
          <div className="space-y-8">
            <div>
              <h1 className="mb-6 text-3xl font-bold text-gray-900">{branch.name}</h1>
              <ActionGuard permission={PERMISSIONS.ORG_BRANCHES_MANAGE_ANY}>
                <BranchForm branch={branch} />
              </ActionGuard>
            </div>

            <div>
              <h2 className="mb-3 text-xl font-semibold text-gray-900">
                {t('admin.organisation.calendar.working_hours_heading')}
              </h2>
              <ActionGuard permission={PERMISSIONS.ORG_BRANCHES_MANAGE_ANY}>
                <BranchWorkingHoursEditor branchId={branch.id} initialDays={days} holidays={holidays} />
              </ActionGuard>
            </div>

            <div>
              <h2 className="mb-3 text-xl font-semibold text-gray-900">
                {t('admin.organisation.calendar.holidays_heading')}
              </h2>
              <ActionGuard permission={PERMISSIONS.ORG_BRANCHES_MANAGE_ANY}>
                <BranchHolidaysPanel branchId={branch.id} holidays={holidays} />
              </ActionGuard>
            </div>
          </div>
        );
      }}
    </AsyncBoundary>
  );
}
