import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetAuthPolicy } from '@/api/generated/security/security';
import { normaliseApiError } from '@/api/http/errors';
import { adminErrorMessageKey } from '../api/errorCodes';
import { useUpdateAuthPolicy } from '../api/wire';

// `AuthPolicyController` manages exactly one field, `require_two_factor` —
// there is no default-locale field anywhere in the API (the plan's
// instruction to put a locale control here does not match the real
// backend; see .squad/gaps/36-483.md #11). Branding is deferred for the
// same reason the plan already calls out (no backend endpoint).
export function AdminSettingsPanel() {
  const { t } = useTranslation();
  const policyQuery = useGetAuthPolicy() as unknown as UseQueryResult<Record<string, unknown>, unknown>;
  const [requireTwoFactor, setRequireTwoFactor] = useState(false);
  const [error, setError] = useState<string | undefined>();
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    if (policyQuery.data && typeof policyQuery.data.require_two_factor === 'boolean') {
      setRequireTwoFactor(policyQuery.data.require_two_factor);
    }
  }, [policyQuery.data]);

  const mutation = useUpdateAuthPolicy({
    onSuccess: () => {
      setError(undefined);
      setSaved(true);
    },
    onError: (err) => {
      setSaved(false);
      setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined)));
    },
  });

  return (
    <div className="max-w-lg space-y-4">
      <div className="rounded border border-gray-200 bg-white p-4">
        <label className="flex items-center gap-2 text-sm text-gray-700">
          <input
            type="checkbox"
            checked={requireTwoFactor}
            onChange={(event) => {
              setSaved(false);
              setRequireTwoFactor(event.target.checked);
            }}
          />
          {t('admin.security.settings.require_two_factor_label')}
        </label>

        {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
        {saved && <p className="mt-2 text-sm text-green-700">{t('admin.security.settings.saved')}</p>}

        <button
          type="button"
          onClick={() => mutation.mutate({ requireTwoFactor })}
          disabled={mutation.isPending}
          className="mt-3 rounded bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
        >
          {t('admin.organisation.actions.save')}
        </button>
      </div>

      {/* TODO(FE-06): branding requires a backend settings endpoint */}
      {/* TODO(FE-06): default locale requires a backend settings endpoint — AuthPolicyController has no locale field */}
    </div>
  );
}
