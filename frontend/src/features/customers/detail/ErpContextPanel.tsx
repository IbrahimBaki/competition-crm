import { useQuery } from '@tanstack/react-query';
import { useTranslation } from 'react-i18next';
import { apiRequest } from '@/api/http/mutator';
import { normaliseApiError, type NormalisedApiError } from '@/api/http/errors';

interface ErpContext {
  legalName: string | null;
  accountStatus: string | null;
  creditHold: boolean | null;
  outstandingBalance: number | null;
  serviceTier: string | null;
  contractEndDate: string | null;
}

interface ErpContextResponse {
  customerId: string;
  context: ErpContext | null;
}

// customers/{customer}/erp-context is absent from the generated client
// (frontend/src/api/generated/integrations/integrations.ts has zero `erp`
// matches) and is not documented in docs/api/openapi.yaml either, so
// there's nothing to regenerate. This hand-rolled hook goes through the
// shared `apiRequest` mutator (CSRF + error normalisation), not raw
// fetch/axios.
// TODO: fold into generated client once the ERP endpoint is in openapi.yaml
function useErpContext(customerId: string) {
  return useQuery<ErpContextResponse, unknown>({
    queryKey: ['customers', customerId, 'erp-context'],
    queryFn: () =>
      apiRequest<{ customer_id: string; erp_context: Record<string, unknown> | null }>({
        url: `/customers/${customerId}/erp-context`,
        method: 'GET',
        timeout: 8000,
      }).then((data) => ({
        customerId: data.customer_id,
        context: data.erp_context
          ? {
              legalName: (data.erp_context.legal_name as string | null) ?? null,
              accountStatus: (data.erp_context.account_status as string | null) ?? null,
              creditHold: (data.erp_context.credit_hold as boolean | null) ?? null,
              outstandingBalance: (data.erp_context.outstanding_balance as number | null) ?? null,
              serviceTier: (data.erp_context.service_tier as string | null) ?? null,
              contractEndDate: (data.erp_context.contract_end_date as string | null) ?? null,
            }
          : null,
      })),
    // A hanging ERP backend must never leave a permanent spinner on the
    // profile — no retry, and the request itself times out (see `timeout`
    // above).
    retry: false,
    refetchOnWindowFocus: false,
  });
}

interface ErpContextPanelProps {
  customerUuid: string;
}

// Strictly non-blocking: this panel's failure must never bubble into the
// page-level AsyncBoundary or block the rest of the profile from rendering
// — it manages its own loading/error/empty states entirely locally.
export function ErpContextPanel({ customerUuid }: ErpContextPanelProps) {
  const { t } = useTranslation();
  const query = useErpContext(customerUuid);

  if (query.isLoading) {
    return (
      <section className="rounded border border-gray-200 p-4">
        <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('customers.detail.erp_heading')}</h2>
        <p className="text-sm text-gray-400">{t('customers.erp.loading')}</p>
      </section>
    );
  }

  if (query.isError) {
    const error = normaliseApiError(query.error) as NormalisedApiError;

    // 403 — the agent simply lacks ERP access; render nothing at all, no error.
    if (error.status === 403) {
      return null;
    }

    // 404 / not-found-shaped errors — no ERP record linked.
    if (error.status === 404) {
      return (
        <section className="rounded border border-gray-200 p-4">
          <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('customers.detail.erp_heading')}</h2>
          <p className="text-sm text-gray-400">{t('customers.erp.no_record')}</p>
        </section>
      );
    }

    // Network error / 5xx / timeout — compact inline notice + retry. No
    // modal, no toast storm.
    return (
      <section className="rounded border border-gray-200 p-4">
        <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('customers.detail.erp_heading')}</h2>
        <p className="text-sm text-amber-700">{t('customers.erp.unavailable')}</p>
        <button
          type="button"
          onClick={() => query.refetch()}
          className="mt-2 rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-50"
        >
          {t('customers.erp.retry')}
        </button>
      </section>
    );
  }

  const context = query.data?.context ?? null;

  return (
    <section className="rounded border border-gray-200 p-4">
      <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('customers.detail.erp_heading')}</h2>
      {!context ? (
        <p className="text-sm text-gray-400">{t('customers.erp.no_record')}</p>
      ) : (
        <dl className="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
          <dt className="text-gray-500">{t('customers.erp.legal_name')}</dt>
          <dd className="text-gray-800">{context.legalName ?? '—'}</dd>
          <dt className="text-gray-500">{t('customers.erp.account_status')}</dt>
          <dd className="text-gray-800">{context.accountStatus ?? '—'}</dd>
          <dt className="text-gray-500">{t('customers.erp.credit_hold')}</dt>
          <dd className="text-gray-800">
            {context.creditHold === null ? '—' : context.creditHold ? t('customers.erp.yes') : t('customers.erp.no')}
          </dd>
          <dt className="text-gray-500">{t('customers.erp.outstanding_balance')}</dt>
          <dd className="text-gray-800">{context.outstandingBalance ?? '—'}</dd>
          <dt className="text-gray-500">{t('customers.erp.service_tier')}</dt>
          <dd className="text-gray-800">{context.serviceTier ?? '—'}</dd>
          <dt className="text-gray-500">{t('customers.erp.contract_end_date')}</dt>
          <dd className="text-gray-800">{context.contractEndDate ?? '—'}</dd>
        </dl>
      )}
    </section>
  );
}
