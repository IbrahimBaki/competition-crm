import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetBranches, useGetDepartments } from '@/api/generated/organization/organization';
import type { ApiPage } from '@/api/http/envelope';
import { normaliseApiError } from '@/api/http/errors';
import { adminErrorMessageKey } from '../api/errorCodes';
import {
  toBranch,
  toDepartment,
  useAttachUserToBranch,
  useDetachUserFromBranch,
  useSetPrimaryBranchForUser,
  useAttachUserToDepartment,
  useDetachUserFromDepartment,
} from '../api/wire';

interface UserPlacementPanelProps {
  userId: string;
}

// Write-only by necessity: no endpoint returns a user's current branch or
// department placement (see .squad/gaps/36-483.md #5), so this panel cannot
// show which branches/departments are already attached — it only offers
// attach/detach/set-primary actions against the full branch/department
// lists, each confirmed by a per-row success/error message.
export function UserPlacementPanel({ userId }: UserPlacementPanelProps) {
  const { t } = useTranslation();
  const [branchId, setBranchId] = useState('');
  const [departmentId, setDepartmentId] = useState('');
  const [message, setMessage] = useState<string | undefined>();
  const [error, setError] = useState<string | undefined>();

  const branchesQuery = useGetBranches({ per_page: 100 }) as unknown as UseQueryResult<
    ApiPage<Record<string, unknown>>,
    unknown
  >;
  const departmentsQuery = useGetDepartments({ per_page: 100 }) as unknown as UseQueryResult<
    ApiPage<Record<string, unknown>>,
    unknown
  >;
  const branches = (branchesQuery.data?.items ?? []).map(toBranch);
  const departments = (departmentsQuery.data?.items ?? []).map(toDepartment);

  const onError = (err: unknown) => {
    setMessage(undefined);
    setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined)));
  };
  const onSuccess = (successMessageKey: string) => () => {
    setError(undefined);
    setMessage(t(successMessageKey));
  };

  const attachBranch = useAttachUserToBranch({ onSuccess: onSuccess('admin.security.user.placement.attached'), onError });
  const detachBranch = useDetachUserFromBranch({ onSuccess: onSuccess('admin.security.user.placement.detached'), onError });
  const setPrimary = useSetPrimaryBranchForUser({ onSuccess: onSuccess('admin.security.user.placement.primary_set'), onError });
  const attachDepartment = useAttachUserToDepartment({
    onSuccess: onSuccess('admin.security.user.placement.attached'),
    onError,
  });
  const detachDepartment = useDetachUserFromDepartment({
    onSuccess: onSuccess('admin.security.user.placement.detached'),
    onError,
  });

  const anyPending =
    attachBranch.isPending ||
    detachBranch.isPending ||
    setPrimary.isPending ||
    attachDepartment.isPending ||
    detachDepartment.isPending;

  return (
    <div className="space-y-4 rounded border border-gray-200 bg-white p-4">
      <p className="text-xs text-gray-500">{t('admin.security.user.placement.write_only_note')}</p>

      <div>
        <label htmlFor="placement-branch" className="mb-1 block text-xs font-medium text-gray-600">
          {t('admin.security.user.placement.branch_label')}
        </label>
        <div className="flex flex-wrap items-center gap-2">
          <select
            id="placement-branch"
            value={branchId}
            onChange={(event) => setBranchId(event.target.value)}
            className="rounded border border-gray-300 px-2 py-1.5 text-sm"
          >
            <option value="" disabled>
              {t('admin.common.select_placeholder')}
            </option>
            {branches.map((branch) => (
              <option key={branch.id} value={branch.id}>
                {branch.name}
              </option>
            ))}
          </select>
          <button
            type="button"
            disabled={!branchId || anyPending}
            onClick={() => attachBranch.mutate({ user: userId, branch: branchId })}
            className="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            {t('admin.security.user.placement.attach')}
          </button>
          <button
            type="button"
            disabled={!branchId || anyPending}
            onClick={() => detachBranch.mutate({ user: userId, branch: branchId })}
            className="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            {t('admin.security.user.placement.detach')}
          </button>
          <button
            type="button"
            disabled={!branchId || anyPending}
            onClick={() => setPrimary.mutate({ user: userId, branch: branchId })}
            className="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            {t('admin.security.user.placement.set_primary')}
          </button>
        </div>
      </div>

      <div>
        <label htmlFor="placement-department" className="mb-1 block text-xs font-medium text-gray-600">
          {t('admin.security.user.placement.department_label')}
        </label>
        <div className="flex flex-wrap items-center gap-2">
          <select
            id="placement-department"
            value={departmentId}
            onChange={(event) => setDepartmentId(event.target.value)}
            className="rounded border border-gray-300 px-2 py-1.5 text-sm"
          >
            <option value="" disabled>
              {t('admin.common.select_placeholder')}
            </option>
            {departments.map((department) => (
              <option key={department.id} value={department.id}>
                {department.name}
              </option>
            ))}
          </select>
          <button
            type="button"
            disabled={!departmentId || anyPending}
            onClick={() => attachDepartment.mutate({ user: userId, department: departmentId })}
            className="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            {t('admin.security.user.placement.attach')}
          </button>
          <button
            type="button"
            disabled={!departmentId || anyPending}
            onClick={() => detachDepartment.mutate({ user: userId, department: departmentId })}
            className="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            {t('admin.security.user.placement.detach')}
          </button>
        </div>
      </div>

      {message && <p className="text-sm text-green-700">{message}</p>}
      {error && <p className="text-sm text-red-600">{error}</p>}
    </div>
  );
}
