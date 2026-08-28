import { useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetBranches } from '@/api/generated/organization/organization';
import type { ApiPage } from '@/api/http/envelope';
import { normaliseApiError } from '@/api/http/errors';
import { adminErrorMessageKey } from '../api/errorCodes';
import { useCreateDepartment, useUpdateDepartment, toBranch } from '../api/wire';
import type { Department } from '../types';

interface DepartmentFormProps {
  department?: Department;
}

// Create requires both locales. Edit only receives the active-locale name
// (DepartmentResource resolves it to a single string server-side — see
// .squad/gaps/36-483.md #1), so the other locale's field starts blank and
// stays required rather than being silently copied from the one value we do
// have.
export function DepartmentForm({ department }: DepartmentFormProps) {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const isEdit = Boolean(department);
  const activeLocale = i18n.language.startsWith('ar') ? 'ar' : 'en';

  const branchesQuery = useGetBranches(
    { per_page: 100 },
    { query: { enabled: !isEdit } }
  ) as unknown as UseQueryResult<ApiPage<Record<string, unknown>>, unknown>;
  const branches = (branchesQuery.data?.items ?? []).map(toBranch).filter((b) => b.isActive);

  const [branchId, setBranchId] = useState('');
  const [nameAr, setNameAr] = useState(activeLocale === 'ar' ? (department?.name ?? '') : '');
  const [nameEn, setNameEn] = useState(activeLocale === 'en' ? (department?.name ?? '') : '');
  const [code, setCode] = useState(department?.code ?? '');
  const [error, setError] = useState<string | undefined>();

  const createMutation = useCreateDepartment({
    onSuccess: (created) => navigate(`/admin/departments/${created.id}`),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const updateMutation = useUpdateDepartment({
    onSuccess: () => navigate(`/admin/departments/${department?.id}`),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const isPending = createMutation.isPending || updateMutation.isPending;
  const canSubmit =
    nameAr.trim().length > 0 && nameEn.trim().length > 0 && code.trim().length > 0 && (isEdit || branchId.length > 0);

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    setError(undefined);
    const name = { ar: nameAr.trim(), en: nameEn.trim() };

    if (isEdit && department) {
      updateMutation.mutate({ department: department.id, name, code: code.trim() });
    } else {
      createMutation.mutate({ branchId, name, code: code.trim() });
    }
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-lg space-y-4">
      {!isEdit && (
        <div>
          <label htmlFor="department-branch" className="mb-1 block text-xs font-medium text-gray-600">
            {t('admin.organisation.department.branch_label')}
          </label>
          <select
            id="department-branch"
            value={branchId}
            onChange={(event) => setBranchId(event.target.value)}
            required
            className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
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
        </div>
      )}

      <div className="grid grid-cols-2 gap-3">
        <div>
          <label htmlFor="department-name-ar" className="mb-1 block text-xs font-medium text-gray-600">
            {t('admin.organisation.name_ar_label')}
          </label>
          <input
            id="department-name-ar"
            value={nameAr}
            onChange={(event) => setNameAr(event.target.value)}
            dir="rtl"
            required
            className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
          />
        </div>
        <div>
          <label htmlFor="department-name-en" className="mb-1 block text-xs font-medium text-gray-600">
            {t('admin.organisation.name_en_label')}
          </label>
          <input
            id="department-name-en"
            value={nameEn}
            onChange={(event) => setNameEn(event.target.value)}
            dir="ltr"
            required
            className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
          />
        </div>
      </div>

      <div>
        <label htmlFor="department-code" className="mb-1 block text-xs font-medium text-gray-600">
          {t('admin.organisation.code_label')}
        </label>
        <input
          id="department-code"
          value={code}
          onChange={(event) => setCode(event.target.value)}
          required
          disabled={isEdit}
          pattern="[a-z0-9-]{2,32}"
          className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm disabled:bg-gray-100"
        />
      </div>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <button
        type="submit"
        disabled={!canSubmit || isPending}
        className="rounded bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
      >
        {t(isEdit ? 'admin.organisation.actions.save' : 'admin.organisation.actions.create')}
      </button>
    </form>
  );
}
