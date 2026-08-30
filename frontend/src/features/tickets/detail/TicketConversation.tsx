import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { keepPreviousData, useQueryClient, type UseQueryResult } from '@tanstack/react-query';
import {
  useGetTicketMessages,
  useGetTicketMessageDeliveryEvents,
  getGetTicketMessagesQueryKey,
} from '@/api/generated/ticketing/ticketing';
import type { ApiPage } from '@/api/http/envelope';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { useRetryTicketMessage } from '../api/wire';
import { useTicketMutation } from '../useTicketMutation';
import type { TicketMessage } from '../types';

const PAGE_SIZE_STEP = 25;
const MAX_PAGE_SIZE = 100;

interface TicketConversationProps {
  ticketId: string;
}

export function TicketConversation({ ticketId }: TicketConversationProps) {
  const { t } = useTranslation();
  const [pageSize, setPageSize] = useState(PAGE_SIZE_STEP);
  const [expandedDelivery, setExpandedDelivery] = useState<string | null>(null);

  const query = useGetTicketMessages(
    ticketId,
    { per_page: pageSize, sort: 'created_at' },
    { query: { enabled: !!ticketId, placeholderData: keepPreviousData } }
  ) as unknown as UseQueryResult<ApiPage<TicketMessage>, unknown>;

  return (
    <section className="rounded border border-gray-200 p-4">
      <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('tickets.detail.conversation_heading')}</h2>
      <AsyncBoundary
        query={query}
        isEmpty={(page) => page.items.length === 0}
        empty={<EmptyState title={t('tickets.conversation.empty_title')} />}
      >
        {(page) => (
          <div className="flex flex-col gap-3">
            {page.meta.total > page.items.length && (
              <button
                type="button"
                onClick={() => setPageSize((size) => Math.min(size + PAGE_SIZE_STEP, MAX_PAGE_SIZE))}
                className="self-center text-sm text-blue-600 hover:underline"
              >
                {t('tickets.conversation.load_earlier')}
              </button>
            )}

            {page.items.map((message) => (
              <MessageRow
                key={message.uuid}
                ticketId={ticketId}
                message={message}
                expanded={expandedDelivery === message.uuid}
                onToggleDelivery={() =>
                  setExpandedDelivery((current) => (current === message.uuid ? null : message.uuid))
                }
              />
            ))}
          </div>
        )}
      </AsyncBoundary>
    </section>
  );
}

interface MessageRowProps {
  ticketId: string;
  message: TicketMessage;
  expanded: boolean;
  onToggleDelivery: () => void;
}

function MessageRow({ ticketId, message, expanded, onToggleDelivery }: MessageRowProps) {
  const { t } = useTranslation();
  const queryClient = useQueryClient();
  const retry = useTicketMutation(useRetryTicketMessage, ticketId, {
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: getGetTicketMessagesQueryKey(ticketId) });
    },
  });

  const canRetry = message.delivery_state === 'failed';

  return (
    <div
      className={`rounded border p-3 ${
        message.is_internal ? 'border-amber-300 bg-amber-50' : 'border-gray-200 bg-white'
      }`}
    >
      <div className="mb-1 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
        <span>
          {t(`tickets.conversation.author_type.${message.author_type}`)} ·{' '}
          {new Date(message.created_at).toLocaleString()}
        </span>
        {message.is_internal && (
          <span className="rounded bg-amber-200 px-2 py-0.5 font-medium text-amber-900">
            {t('tickets.composer.internalWarning')}
          </span>
        )}
      </div>

      <p className="whitespace-pre-wrap text-sm text-gray-900">{message.body}</p>

      {/* Historical attachments cannot be shown here: GET /tickets/{ticket}/messages
          returns raw, unwrapped models (no TicketMessageResource::collection()),
          so the attachments relation is never serialized. See story gap notes. */}

      {!message.is_internal && message.delivery_state && (
        <div className="mt-2 flex items-center gap-2 text-xs text-gray-500">
          <span>{t(`tickets.conversation.delivery_state.${message.delivery_state}`)}</span>
          <button type="button" onClick={onToggleDelivery} className="text-blue-600 hover:underline">
            {t('tickets.conversation.view_delivery_events')}
          </button>
          {canRetry && (
            <ActionGuard permission={PERMISSIONS.TICKET_MESSAGE_RETRY}>
              <button
                type="button"
                disabled={retry.isPending}
                onClick={() => retry.mutate({ ticket: ticketId, message: message.uuid })}
                className="text-blue-600 hover:underline disabled:opacity-50"
              >
                {t('tickets.conversation.retry')}
              </button>
            </ActionGuard>
          )}
        </div>
      )}

      {expanded && <DeliveryEvents ticketId={ticketId} messageId={message.uuid} />}
    </div>
  );
}

function DeliveryEvents({ ticketId, messageId }: { ticketId: string; messageId: string }) {
  const { t } = useTranslation();
  const query = useGetTicketMessageDeliveryEvents(ticketId, messageId, undefined, {
    query: { enabled: !!ticketId && !!messageId },
  }) as unknown as UseQueryResult<
    ApiPage<{ uuid: string; from_state: string | null; to_state: string; occurred_at: string }>,
    unknown
  >;

  if (query.isLoading) return <p className="mt-2 text-xs text-gray-400">{t('tickets.conversation.loading')}</p>;
  if (query.isError || !query.data) return null;

  return (
    <ul className="mt-2 border-t border-gray-100 pt-2 text-xs text-gray-500">
      {query.data.items.map((event) => (
        <li key={event.uuid}>
          {event.from_state ?? '–'} → {event.to_state} ({new Date(event.occurred_at).toLocaleString()})
        </li>
      ))}
    </ul>
  );
}
