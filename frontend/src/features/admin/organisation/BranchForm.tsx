import { useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { normaliseApiError } from '@/api/http/errors';
import { adminErrorMessageKey } from '../api/errorCodes';
import { useCreateBranch, useUpdateBranch } from '../api/wire';
import { BranchTimezoneField } from './calendar/BranchTimezoneField';
import type { Branch } from '../types';

interface BranchFormProps {
  branch?: Branch;
}

// Create requires both locales. Edit only receives the active-locale name
// from the API (BranchResource resolves to a single string server-side —
// see .squad/gaps/frontend/36-483.md #1), so the other locale's field starts
// blank and stays required rather than being silently copied from the one
// value we do have.
export function BranchForm({ branch }: BranchFormProps) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const isEdit = Boolean(branch);
  const [nameAr, setNameAr] = useState(branch?.nameAr ?? '');
  const [nameEn, setNameEn] = useState(branch?.nameEn ?? '');
  const [code, setCode] = useState(branch?.code ?? '');
  const [timezone, setTimezone] = useState(branch?.timezone ?? '');
  const [is24x7, setIs24x7] = useState(branch?.is24x7 ?? false);
  const [error, setError] = useState<string | undefined>();

  const createMutation = useCreateBranch({
    onSuccess: (created) => navigate(`/admin/branches/${created.id}`),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const updateMutation = useUpdateBranch({
    onSuccess: () => navigate(`/admin/branches/${branch?.id}`),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const isPending = createMutation.isPending || updateMutation.isPending;
  const canSubmit = nameAr.trim().length > 0 && nameEn.trim().length > 0 && code.trim().length > 0 && timezone.length > 0;

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    setError(undefined);
    const name = { ar: nameAr.trim(), en: nameEn.trim() };

    if (isEdit && branch) {
      updateMutation.mutate({ branch: branch.id, name, code: code.trim(), timezone, is24x7 });
    } else {
      createMutation.mutate({ name, code: code.trim(), timezone, is24x7 });
    }
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-lg space-y-4">
      <div className="grid grid-cols-2 gap-3">
        <div>
          <label htmlFor="branch-name-ar" className="mb-1 block text-xs font-medium text-gray-600">
            {t('admin.organisation.branch.name_ar_label')}
          </label>
          <input
            id="branch-name-ar"
            value={nameAr}
            onChange={(event) => setNameAr(event.target.value)}
            dir="rtl"
            required
            className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
          />
        </div>
        <div>
          <label htmlFor="branch-name-en" className="mb-1 block text-xs font-medium text-gray-600">
            {t('admin.organisation.branch.name_en_label')}
          </label>
          <input
            id="branch-name-en"
            value={nameEn}
            onChange={(event) => setNameEn(event.target.value)}
            dir="ltr"
            required
            className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
          />
        </div>
      </div>

      <div>
        <label htmlFor="branch-code" className="mb-1 block text-xs font-medium text-gray-600">
          {t('admin.organisation.branch.code_label')}
        </label>
        <input
          id="branch-code"
          value={code}
          onChange={(event) => setCode(event.target.value)}
          required
          disabled={isEdit}
          pattern="[a-z0-9-]{2,32}"
          className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm disabled:bg-gray-100"
        />
      </div>

      <BranchTimezoneField id="branch-timezone" value={timezone} onChange={setTimezone} required />

      <label className="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" checked={is24x7} onChange={(event) => setIs24x7(event.target.checked)} />
        {t('admin.organisation.branch.is_24_7_label')}
      </label>

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
