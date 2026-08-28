import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetTicketHistory, useGetTicketLinks } from '@/api/generated/ticketing/ticketing';
import type { ApiPage } from '@/api/http/envelope';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import type { TicketEvent, TicketLink } from '../types';

interface TicketHistoryPanelProps {
  ticketId: string;
}

export function TicketHistoryPanel({ ticketId }: TicketHistoryPanelProps) {
  const { t } = useTranslation();
  const [isOpen, setIsOpen] = useState(false);

  return (
    <section className="rounded border border-gray-200 p-4">
      <button
        type="button"
        onClick={() => setIsOpen((open) => !open)}
        className="flex w-full items-center justify-between text-sm font-semibold text-gray-700"
      >
        {t('tickets.history.heading')}
        <span>{isOpen ? '▲' : '▼'}</span>
      </button>

      {isOpen && (
        <ActionGuard permission={PERMISSIONS.TICKETS_HISTORY_VIEW}>
          <div className="mt-3 flex flex-col gap-4">
            <EventsList ticketId={ticketId} />
            <LinksList ticketId={ticketId} />
          </div>
        </ActionGuard>
      )}
    </section>
  );
}

function EventsList({ ticketId }: { ticketId: string }) {
  const { t } = useTranslation();
  const query = useGetTicketHistory(ticketId, { per_page: 25 }, { query: { enabled: !!ticketId } }) as unknown as UseQueryResult<
    ApiPage<TicketEvent>,
    unknown
  >;

  return (
    <div>
      <h3 className="mb-1 text-xs font-semibold uppercase text-gray-500">{t('tickets.history.events_heading')}</h3>
      <AsyncBoundary
        query={query}
        isEmpty={(page) => page.items.length === 0}
        empty={<EmptyState title={t('tickets.history.events_empty')} />}
      >
        {(page) => (
          <ul className="flex flex-col gap-1 text-xs text-gray-600">
            {page.items.map((event) => (
              <li key={event.uuid}>
                <span className="text-gray-400">
                  {event.occurred_at ? new Date(event.occurred_at).toLocaleString() : '—'}
                </span>{' '}
                {t(`tickets.history.event_type.${event.type}`, { defaultValue: event.type ?? '' })}
              </li>
            ))}
          </ul>
        )}
      </AsyncBoundary>
    </div>
  );
}

function LinksList({ ticketId }: { ticketId: string }) {
  const { t } = useTranslation();
  const query = useGetTicketLinks(ticketId, { per_page: 25 }, { query: { enabled: !!ticketId } }) as unknown as UseQueryResult<
    ApiPage<TicketLink>,
    unknown
  >;

  return (
    <ActionGuard permission={PERMISSIONS.TICKETS_LINK}>
      <div>
        <h3 className="mb-1 text-xs font-semibold uppercase text-gray-500">{t('tickets.history.links_heading')}</h3>
        <AsyncBoundary
          query={query}
          isEmpty={(page) => page.items.length === 0}
          empty={<EmptyState title={t('tickets.history.links_empty')} />}
        >
          {(page) => (
            <ul className="flex flex-col gap-1 text-xs text-gray-600">
              {page.items.map((link) => (
                <li key={link.uuid}>
                  {t(`tickets.history.link_relation.${link.relation}`)} · {new Date(link.created_at).toLocaleDateString()}
                </li>
              ))}
            </ul>
          )}
        </AsyncBoundary>
      </div>
    </ActionGuard>
  );
}
