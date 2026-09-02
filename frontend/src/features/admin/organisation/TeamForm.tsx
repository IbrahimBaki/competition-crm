import { useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetDepartments } from '@/api/generated/organization/organization';
import { normaliseApiError } from '@/api/http/errors';
import { adminErrorMessageKey } from '../api/errorCodes';
import { useCreateTeam, useUpdateTeam, toDepartment } from '../api/wire';
import type { Team } from '../types';

interface TeamFormProps {
  team?: Team;
}

export function TeamForm({ team }: TeamFormProps) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const isEdit = Boolean(team);

  const [departmentId, setDepartmentId] = useState(team?.departmentId ?? '');
  const [nameAr, setNameAr] = useState(team?.nameAr ?? '');
  const [nameEn, setNameEn] = useState(team?.nameEn ?? '');
  const [code, setCode] = useState(team?.code ?? '');
  const [error, setError] = useState<string | undefined>();

  const departmentsQuery = useGetDepartments({ per_page: 100 }) as unknown as UseQueryResult<
    { items: Record<string, unknown>[] },
    unknown
  >;
  const departments = (departmentsQuery.data?.items ?? []).map(toDepartment);

  const createMutation = useCreateTeam({
    onSuccess: (created) => navigate(`/admin/teams/${created.id}`),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const updateMutation = useUpdateTeam({
    onSuccess: () => navigate(`/admin/teams/${team?.id}`),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const isPending = createMutation.isPending || updateMutation.isPending;
  const canSubmit =
    nameAr.trim().length > 0 && nameEn.trim().length > 0 && code.trim().length > 0 && departmentId.length > 0;

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    setError(undefined);
    const name = { ar: nameAr.trim(), en: nameEn.trim() };

    if (isEdit && team) {
      updateMutation.mutate({ team: team.id, name, code: code.trim() });
    } else {
      createMutation.mutate({ departmentId, name, code: code.trim() });
    }
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-lg space-y-4">
      <div>
        <label htmlFor="team-department" className="mb-1 block text-xs font-medium text-gray-600">
          {t('admin.organisation.team.department_label')}
        </label>
        <select
          id="team-department"
          value={departmentId}
          onChange={(event) => setDepartmentId(event.target.value)}
          required
          disabled={isEdit}
          className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm disabled:bg-gray-100"
        >
          <option value="" disabled>
            {t('admin.organisation.team.department_placeholder')}
          </option>
          {departments.map((department) => (
            <option key={department.id} value={department.id}>
              {department.name}
            </option>
          ))}
        </select>
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div>
          <label htmlFor="team-name-ar" className="mb-1 block text-xs font-medium text-gray-600">
            {t('admin.organisation.name_ar_label')}
          </label>
          <input
            id="team-name-ar"
            value={nameAr}
            onChange={(event) => setNameAr(event.target.value)}
            dir="rtl"
            required
            className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
          />
        </div>
        <div>
          <label htmlFor="team-name-en" className="mb-1 block text-xs font-medium text-gray-600">
            {t('admin.organisation.name_en_label')}
          </label>
          <input
            id="team-name-en"
            value={nameEn}
            onChange={(event) => setNameEn(event.target.value)}
            dir="ltr"
            required
            className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
          />
        </div>
      </div>

      <div>
        <label htmlFor="team-code" className="mb-1 block text-xs font-medium text-gray-600">
          {t('admin.organisation.code_label')}
        </label>
        <input
          id="team-code"
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
