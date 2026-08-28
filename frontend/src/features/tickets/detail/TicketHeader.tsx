import { useTranslation } from 'react-i18next';
import { useAuth } from '@/auth/AuthProvider';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { useWatchTicket, useUnwatchTicket } from '../api/wire';
import { useTicketMutation } from '../useTicketMutation';
import { statusBadgeClass, priorityBadgeClass, priorityLabelKey } from '../utils/labels';
import { pickBilingual } from '../utils/bilingual';
import type { TicketDetail } from '../types';

interface TicketHeaderProps {
  ticket: TicketDetail;
}

export function TicketHeader({ ticket }: TicketHeaderProps) {
  const { t, i18n } = useTranslation();
  const { user } = useAuth();

  const watch = useTicketMutation(useWatchTicket, ticket.id);
  const unwatch = useTicketMutation(useUnwatchTicket, ticket.id);

  const toggleWatch = () => {
    if (ticket.is_watched) {
      unwatch.mutate({ ticket: ticket.id, userId: user?.id ?? '' });
    } else {
      watch.mutate({ ticket: ticket.id });
    }
  };

  return (
    <div className="rounded border border-gray-200 p-4">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <div>
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">{ticket.reference}</p>
          <h1 className="text-xl font-semibold text-gray-900">{ticket.subject}</h1>
        </div>

        <ActionGuard permission={PERMISSIONS.WORKSPACE_TICKET_WATCHERS_VIEW}>
          <button
            type="button"
            onClick={toggleWatch}
            disabled={watch.isPending || unwatch.isPending}
            className="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50 disabled:opacity-50"
          >
            {ticket.is_watched ? t('tickets.detail.unwatch') : t('tickets.detail.watch')}
          </button>
        </ActionGuard>
      </div>

      <div className="mt-3 flex flex-wrap items-center gap-2">
        {ticket.status && (
          <span
            className={`rounded px-2 py-0.5 text-xs font-medium ${statusBadgeClass(ticket.status.lifecycle_type)}`}
          >
            {pickBilingual(ticket.status.name, i18n.language)}
          </span>
        )}
        {ticket.priority && (
          <span className={`rounded px-2 py-0.5 text-xs font-medium ${priorityBadgeClass(ticket.priority)}`}>
            {t(priorityLabelKey(ticket.priority))}
          </span>
        )}
        {ticket.assignee_id ? (
          <span className="text-xs text-gray-500">
            {t('tickets.detail.assigned_to')}: <code>{ticket.assignee_id}</code>
          </span>
        ) : (
          <span className="text-xs text-gray-400">{t('tickets.detail.unassigned')}</span>
        )}
      </div>
    </div>
  );
}
