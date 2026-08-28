import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetCustomer, useGetCustomerTimeline } from '@/api/generated/customers/customers';
import type { GetCustomerTimelineParams } from '@/api/generated/model/getCustomerTimelineParams';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { LoadingState } from '@/shell/states/LoadingState';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { usePermissions } from '@/auth/usePermissions';

interface CustomerSummary {
  uuid: string;
  name: string;
  status: string;
}

interface TimelineEntry {
  id: string;
  source: string;
  type: string;
  occurred_at: string;
}

interface CustomerContextPanelProps {
  customerId: string | null;
}

// Read-only in this story; deep link opens the customer screen separately.
export function CustomerContextPanel({ customerId }: CustomerContextPanelProps) {
  const { t } = useTranslation();

  if (!customerId) {
    return (
      <section className="rounded border border-gray-200 p-4 text-sm text-gray-400">
        {t('tickets.customer_context.none')}
      </section>
    );
  }

  return (
    <ActionGuard permission={PERMISSIONS.CUSTOMERS_VIEW}>
      <CustomerContextPanelContent customerId={customerId} />
    </ActionGuard>
  );
}

function CustomerContextPanelContent({ customerId }: { customerId: string }) {
  const { t } = useTranslation();

  const { can } = usePermissions();

  const customerQuery = useGetCustomer(customerId) as unknown as UseQueryResult<CustomerSummary, unknown>;

  // The real endpoint paginates by `limit`/`before` (see
  // CustomerTimelineController::index), not page/per_page as
  // getCustomerTimelineParams.ts declares.
  const timelineQuery = useGetCustomerTimeline(customerId, { limit: 10 } as unknown as GetCustomerTimelineParams, {
    query: { enabled: can(PERMISSIONS.CUSTOMERS_TIMELINE_VIEW) },
  }) as unknown as UseQueryResult<TimelineEntry[], unknown>;

  return (
    <section className="rounded border border-gray-200 p-4">
      <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('tickets.customer_context.heading')}</h2>
      <AsyncBoundary query={customerQuery}>
        {(customer) => (
          <div>
            <p className="font-medium text-gray-900">{customer.name}</p>
            <p className="text-xs text-gray-500">{t(`tickets.customer_context.status.${customer.status}`)}</p>
            <Link
              to={`/customers/${customer.uuid}`}
              target="_blank"
              rel="noopener noreferrer"
              className="mt-2 inline-block text-xs text-blue-600 hover:underline"
            >
              {t('tickets.customer_context.open_profile')}
            </Link>
          </div>
        )}
      </AsyncBoundary>

      <ActionGuard permission={PERMISSIONS.CUSTOMERS_TIMELINE_VIEW}>
        <div className="mt-4">
          <h3 className="mb-2 text-xs font-semibold uppercase text-gray-500">
            {t('tickets.customer_context.timeline_heading')}
          </h3>
          <AsyncBoundary
            query={timelineQuery}
            isEmpty={(entries) => entries.length === 0}
            loading={<LoadingState rows={2} label={t('tickets.conversation.loading')} />}
            empty={<p className="text-xs text-gray-400">{t('tickets.customer_context.timeline_empty')}</p>}
          >
            {(entries) => (
              <ul className="flex flex-col gap-1 text-xs text-gray-600">
                {entries.map((entry) => (
                  <li key={entry.id}>
                    <span className="text-gray-400">{new Date(entry.occurred_at).toLocaleDateString()}</span>{' '}
                    {t(`tickets.customer_context.timeline_type.${entry.type}`, { defaultValue: entry.type })}
                  </li>
                ))}
              </ul>
            )}
          </AsyncBoundary>
        </div>
      </ActionGuard>
    </section>
  );
}
