import { useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetTicket } from '@/api/generated/ticketing/ticketing';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { NotFoundState } from '@/shell/states/NotFoundState';
import { ForbiddenState } from '@/shell/states/ForbiddenState';
import type { TicketDetail } from '@/features/tickets/types';
import { TicketHeader } from '@/features/tickets/detail/TicketHeader';
import { TicketConversation } from '@/features/tickets/detail/TicketConversation';
import { TicketComposer } from '@/features/tickets/detail/TicketComposer';
import { TicketPropertiesPanel } from '@/features/tickets/detail/TicketPropertiesPanel';
import { TicketStatusControl } from '@/features/tickets/detail/TicketStatusControl';
import { TicketAssignmentControl } from '@/features/tickets/detail/TicketAssignmentControl';
import { TicketSlaPanel } from '@/features/tickets/detail/TicketSlaPanel';
import { CustomerContextPanel } from '@/features/tickets/detail/CustomerContextPanel';
import { TicketHistoryPanel } from '@/features/tickets/detail/TicketHistoryPanel';

export function TicketDetailPage() {
  const { ticketId } = useParams<{ ticketId: string }>();
  const { t } = useTranslation();

  const query = useGetTicket(ticketId ?? '', {
    query: { enabled: !!ticketId },
  }) as unknown as UseQueryResult<TicketDetail, unknown>;

  if (!ticketId) return <NotFoundState />;

  return (
    <AsyncBoundary
      query={query}
      error={
        (query.error as { status?: number } | undefined)?.status === 404 ? (
          <NotFoundState />
        ) : (query.error as { status?: number } | undefined)?.status === 403 ? (
          <ForbiddenState />
        ) : undefined
      }
    >
      {(ticket) => (
        <div className="grid grid-cols-1 gap-4 lg:grid-cols-[280px_1fr_320px]">
          <div className="order-3 lg:order-1">
            <CustomerContextPanel customerId={ticket.customer_id} />
          </div>

          <div className="order-1 flex flex-col gap-4 lg:order-2">
            <TicketHeader ticket={ticket} />
            <TicketConversation ticketId={ticket.id} />
            <TicketComposer ticket={ticket} />
            <TicketHistoryPanel ticketId={ticket.id} />
          </div>

          <div className="order-2 flex flex-col gap-4 lg:order-3">
            <section className="rounded border border-gray-200 p-4">
              <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('tickets.detail.status_heading')}</h2>
              <TicketStatusControl ticket={ticket} />
            </section>
            <section className="rounded border border-gray-200 p-4">
              <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('tickets.detail.assignment_heading')}</h2>
              <TicketAssignmentControl ticket={ticket} />
            </section>
            <section className="rounded border border-gray-200 p-4">
              <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('tickets.detail.properties_heading')}</h2>
              <TicketPropertiesPanel ticket={ticket} />
            </section>
            <section className="rounded border border-gray-200 p-4">
              <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('tickets.detail.sla_heading')}</h2>
              <TicketSlaPanel sla={ticket.sla} />
            </section>
          </div>
        </div>
      )}
    </AsyncBoundary>
  );
}
