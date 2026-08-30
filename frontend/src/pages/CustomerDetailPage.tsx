import { useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetCustomer } from '@/api/generated/customers/customers';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { NotFoundState } from '@/shell/states/NotFoundState';
import { ForbiddenState } from '@/shell/states/ForbiddenState';
import { RequirePermission } from '@/auth/RequirePermission';
import { PERMISSIONS } from '@/auth/permissions';
import { toCustomerDetail } from '@/features/customers/api/wire';
import { CustomerHeader } from '@/features/customers/detail/CustomerHeader';
import { CustomerDangerActions } from '@/features/customers/detail/CustomerDangerActions';
import { CustomerIdentityPanel } from '@/features/customers/detail/CustomerIdentityPanel';
import { CustomerNotesPanel } from '@/features/customers/detail/CustomerNotesPanel';
import { CustomerAttachmentsPanel } from '@/features/customers/detail/CustomerAttachmentsPanel';
import { CustomerTimeline } from '@/features/customers/detail/CustomerTimeline';
import { ErpContextPanel } from '@/features/customers/detail/ErpContextPanel';
import { DuplicateCandidatesPanel } from '@/features/customers/duplicates/DuplicateCandidatesPanel';
import { ActionGuard } from '@/shell/ActionGuard';

export function CustomerDetailPage() {
  const { customerId } = useParams<{ customerId: string }>();
  const { t } = useTranslation();

  const rawQuery = useGetCustomer(customerId ?? '', {
    query: { enabled: !!customerId },
  }) as unknown as UseQueryResult<unknown, unknown>;

  const query = {
    ...rawQuery,
    data: rawQuery.data ? toCustomerDetail(rawQuery.data) : undefined,
  } as UseQueryResult<ReturnType<typeof toCustomerDetail>, unknown>;

  if (!customerId) return <NotFoundState />;

  return (
    <RequirePermission permission={PERMISSIONS.CUSTOMERS_VIEW} fallback={<ForbiddenState />}>
      <AsyncBoundary
        query={query}
        error={
          (rawQuery.error as { status?: number } | undefined)?.status === 404 ? (
            <NotFoundState />
          ) : (rawQuery.error as { status?: number } | undefined)?.status === 403 ? (
            <ForbiddenState />
          ) : undefined
        }
      >
        {(customer) => (
          <div className="flex flex-col gap-4">
            <CustomerHeader customer={customer} actions={<CustomerDangerActions customer={customer} />} />

            <div className="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_1fr]">
              <div className="flex flex-col gap-4">
                <CustomerIdentityPanel customerUuid={customer.uuid} />
                <CustomerNotesPanel customerUuid={customer.uuid} />
                <CustomerAttachmentsPanel customerUuid={customer.uuid} />
                <ActionGuard permission={PERMISSIONS.CUSTOMERS_DUPLICATE_VIEW}>
                  <DuplicateCandidatesPanel customerUuid={customer.uuid} />
                </ActionGuard>
              </div>

              <div className="flex flex-col gap-4">
                <ErpContextPanel customerUuid={customer.uuid} />
                <section className="rounded border border-gray-200 p-4">
                  <h2 className="mb-3 text-sm font-semibold text-gray-700">
                    {t('customers.detail.timeline_heading')}
                  </h2>
                  <ActionGuard permission={PERMISSIONS.CUSTOMERS_TIMELINE_VIEW}>
                    <CustomerTimeline customerUuid={customer.uuid} />
                  </ActionGuard>
                </section>
              </div>
            </div>
          </div>
        )}
      </AsyncBoundary>
    </RequirePermission>
  );
}
