import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { useMyQueueQuery } from '../queues/useMyQueueQuery';
import { WorkspacePanel } from './WorkspacePanel';
import type { ApiPage } from '@/api/http/envelope';
import type { TicketListRow } from '../../tickets/types';

export function MyTicketsPanel() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const query = useMyQueueQuery(10);

  return (
    <WorkspacePanel
      title={t('workspace.panel.my_tickets.title')}
      count={query.data?.meta.total}
      viewAllHref="/tickets?mode=mine"
      viewAllLabel={t('workspace.panel.view_all')}
    >
      <AsyncBoundary<ApiPage<TicketListRow>>
        query={query}
        isEmpty={(data) => data.items.length === 0}
        empty={
          <EmptyState
            title={t('workspace.panel.my_tickets.empty_title')}
            description={t('workspace.panel.my_tickets.empty_description')}
          />
        }
      >
        {(data) => (
          <ul className="divide-y divide-gray-100">
            {data.items.map((ticket) => (
              <li key={ticket.uuid}>
                <button
                  type="button"
                  onClick={() => navigate(`/tickets/${ticket.uuid}`)}
                  className="flex w-full items-center justify-between gap-2 py-2 text-start text-sm hover:bg-gray-50"
                >
                  <span className="truncate">
                    <span className="font-mono text-xs text-gray-400">{ticket.reference}</span>{' '}
                    <span className="text-gray-800">{ticket.subject}</span>
                  </span>
                  <span className="shrink-0 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                    {t(`tickets.status.${ticket.status}`)}
                  </span>
                </button>
              </li>
            ))}
          </ul>
        )}
      </AsyncBoundary>
    </WorkspacePanel>
  );
}
