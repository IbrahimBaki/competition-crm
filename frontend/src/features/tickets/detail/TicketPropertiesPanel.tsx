import { Fragment } from 'react';
import { useTranslation } from 'react-i18next';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { useUpdateTicketPriority } from '../api/wire';
import { useTicketMutation } from '../useTicketMutation';
import { ConflictBanner } from './ConflictBanner';
import { priorityLabelKey } from '../utils/labels';
import type { TicketDetail, TicketPriority } from '../types';

const PRIORITY_OPTIONS: TicketPriority[] = ['low', 'normal', 'high', 'urgent'];

interface TicketPropertiesPanelProps {
  ticket: TicketDetail;
}

// Only `priority` has a real update path (UpdateTicketRequest::rules()).
// Category / department / tags / custom fields are shown read-only: no
// endpoint accepts changes to them today (see api/wire.ts header comment).
export function TicketPropertiesPanel({ ticket }: TicketPropertiesPanelProps) {
  const { t } = useTranslation();
  const mutation = useTicketMutation(useUpdateTicketPriority, ticket.id);

  return (
    <div className="flex flex-col gap-3 text-sm">
      {mutation.conflict && <ConflictBanner error={mutation.conflict} onReload={mutation.reloadLatest} />}

      <div>
        <label htmlFor="ticket-priority" className="mb-1 block text-xs font-medium text-gray-600">
          {t('tickets.properties.priority_label')}
        </label>
        <ActionGuard permission={PERMISSIONS.TICKETS_UPDATE}>
          <select
            id="ticket-priority"
            value={ticket.priority ?? ''}
            disabled={mutation.isPending}
            onChange={(event) =>
              mutation.mutate({ ticket: ticket.id, priority: event.target.value })
            }
            className="w-full rounded border border-gray-300 px-2 py-1"
          >
            {PRIORITY_OPTIONS.map((priority) => (
              <option key={priority} value={priority}>
                {t(priorityLabelKey(priority))}
              </option>
            ))}
          </select>
        </ActionGuard>
      </div>

      <dl className="grid grid-cols-2 gap-x-2 gap-y-1 text-xs text-gray-500">
        <dt>{t('tickets.properties.category_label')}</dt>
        <dd>{ticket.category_id ? t('tickets.properties.read_only') : '—'}</dd>
        <dt>{t('tickets.properties.department_label')}</dt>
        <dd>{ticket.department_id ? t('tickets.properties.read_only') : '—'}</dd>
      </dl>

      {ticket.custom_fields && Object.keys(ticket.custom_fields).length > 0 && (
        <div>
          <p className="mb-1 text-xs font-medium text-gray-600">{t('tickets.properties.custom_fields_label')}</p>
          <dl className="grid grid-cols-2 gap-x-2 gap-y-1 text-xs text-gray-600">
            {Object.entries(ticket.custom_fields).map(([key, value]) => (
              <Fragment key={key}>
                <dt className="text-gray-400">{key}</dt>
                <dd>{String(value)}</dd>
              </Fragment>
            ))}
          </dl>
        </div>
      )}
    </div>
  );
}
