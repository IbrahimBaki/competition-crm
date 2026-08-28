import { useRef, useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient } from '@tanstack/react-query';
import { useGetTicketMessages, getGetTicketMessagesQueryKey } from '@/api/generated/ticketing/ticketing';
import type { GetTicketMessagesParams } from '@/api/generated/model/getTicketMessagesParams';
import { useGetCustomer } from '@/api/generated/customers/customers';
import { ActionGuard } from '@/shell/ActionGuard';
import { usePermissions } from '@/auth/usePermissions';
import { PERMISSIONS } from '@/auth/permissions';
import { useSendTicketMessage, newIdempotencyKey } from '../api/wire';
import { useTicketMutation } from '../useTicketMutation';
import { ConflictBanner } from './ConflictBanner';
import { AttachmentUploader, type UploadedAttachment } from '@/shared/attachments/AttachmentUploader';
import { QuickReplyPicker } from '@/features/workspace/quickReplies/QuickReplyPicker';
import { useInsertQuickReply } from '@/features/workspace/quickReplies/useInsertQuickReply';
import type { TicketDetail, MessageChannel } from '../types';

type ComposerMode = 'public' | 'internal';

interface TicketComposerProps {
  ticket: TicketDetail;
}

export function TicketComposer({ ticket }: TicketComposerProps) {
  const { t, i18n } = useTranslation();
  const { can } = usePermissions();
  const queryClient = useQueryClient();
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  const [mode, setMode] = useState<ComposerMode | null>(null);
  const [body, setBody] = useState('');
  const [attachments, setAttachments] = useState<UploadedAttachment[]>([]);
  const [idempotencyKey, setIdempotencyKey] = useState(() => newIdempotencyKey());
  const [pickerOpen, setPickerOpen] = useState(false);
  const [mentionsInput, setMentionsInput] = useState('');

  const isReadOnly = Boolean(ticket.merged_into_id) || ticket.status?.lifecycle_type === 'spam';

  const send = useTicketMutation(useSendTicketMessage, ticket.id, {
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: getGetTicketMessagesQueryKey(ticket.id) });
      setBody('');
      setAttachments([]);
      setMode(null);
      setMentionsInput('');
      setIdempotencyKey(newIdempotencyKey());
    },
  });

  // Insert at the caret rather than replacing the draft, preserving existing text.
  const insertAtCaret = (text: string) => {
    const textarea = textareaRef.current;
    const start = textarea?.selectionStart ?? body.length;
    const end = textarea?.selectionEnd ?? body.length;
    setBody((current) => current.slice(0, start) + text + current.slice(end));
  };

  const { insert: insertQuickReply } = useInsertQuickReply(insertAtCaret);

  const mentionsAllowed = mode === 'internal' && can(PERMISSIONS.WORKSPACE_TICKET_MESSAGE_MENTION);
  const mentionUuids = mentionsInput
    .split(',')
    .map((value) => value.trim())
    .filter(Boolean);
  const sendError = send.error as { code?: string } | null;
  const mentionError =
    send.fieldErrors.mentions?.join(' ') ||
    (sendError?.code === 'mention.not_allowed_on_public_reply' ? t('tickets.composer.mention_not_allowed') : null);

  // The backend has no concept of a per-reply channel picker: it always
  // requires a `channel` on the message. Internal notes are forced to the
  // "internal" channel server-side regardless of what's sent (see
  // PostTicketMessage::handle). For a public reply we mirror the channel of
  // the most recent inbound customer message so outbound guards (consent,
  // contact-on-file) run against the right channel.
  const lastInboundQuery = useGetTicketMessages(
    ticket.id,
    { per_page: 1, sort: '-created_at', filter: { direction: { eq: 'inbound' } } } as unknown as GetTicketMessagesParams,
    { query: { enabled: mode === 'public' } }
  ) as unknown as { data?: { items: Array<{ channel: MessageChannel }> } };
  const replyChannel: MessageChannel = lastInboundQuery.data?.items[0]?.channel ?? 'email';

  const customerQuery = useGetCustomer(ticket.customer_id ?? '', {
    query: { enabled: mode === 'public' && !!ticket.customer_id },
  }) as unknown as { data?: { name: string } };

  const attachmentsAllClean = attachments.every((attachment) => attachment.scanState === 'clean');
  const canSubmit = mode !== null && body.trim().length > 0 && attachmentsAllClean && !send.isPending;

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    if (!canSubmit || mode === null) return;

    send.mutate({
      ticket: ticket.id,
      body,
      isInternal: mode === 'internal',
      channel: mode === 'internal' ? 'internal' : replyChannel,
      attachmentUuids: attachments.map((attachment) => attachment.uuid),
      mentions: mentionsAllowed && mentionUuids.length > 0 ? mentionUuids : undefined,
      idempotencyKey,
    });
  };

  if (isReadOnly) {
    return (
      <section className="rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">
        {t('tickets.composer.read_only')}
      </section>
    );
  }

  return (
    <ActionGuard permission={PERMISSIONS.TICKET_MESSAGE_SEND}>
      <section
        className={`rounded border p-4 ${mode === 'internal' ? 'border-amber-300 bg-amber-50' : 'border-gray-200'}`}
      >
        {send.conflict && <ConflictBanner error={send.conflict} onReload={send.reloadLatest} />}

        <div className="mb-3 flex gap-2" role="radiogroup" aria-label={t('tickets.composer.mode_label')}>
          <button
            type="button"
            role="radio"
            aria-checked={mode === 'public'}
            onClick={() => setMode('public')}
            className={`rounded px-3 py-1.5 text-sm font-medium ${
              mode === 'public' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'
            }`}
          >
            {t('tickets.composer.mode_public')}
          </button>
          <ActionGuard permission={PERMISSIONS.TICKET_MESSAGE_INTERNAL_WRITE}>
            <button
              type="button"
              role="radio"
              aria-checked={mode === 'internal'}
              onClick={() => setMode('internal')}
              className={`rounded px-3 py-1.5 text-sm font-medium ${
                mode === 'internal' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700'
              }`}
            >
              {t('tickets.composer.mode_internal')}
            </button>
          </ActionGuard>
        </div>

        {mode === 'internal' && (
          <p className="mb-2 rounded bg-amber-200 px-2 py-1 text-xs font-medium text-amber-900">
            {t('tickets.composer.internalWarning')}
          </p>
        )}

        {mode === 'public' && ticket.customer_id && (
          <p className="mb-2 text-xs text-gray-500">
            {t('tickets.composer.sending_to', { name: customerQuery.data?.name ?? '…' })}
          </p>
        )}

        <form onSubmit={handleSubmit}>
          <div className="relative mb-2 flex items-center gap-2">
            <button
              type="button"
              onClick={() => setPickerOpen((current) => !current)}
              className="rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100"
            >
              {t('workspace.quick_replies.insert_button')}
            </button>
            {pickerOpen && (
              <QuickReplyPicker
                onSelect={(replyId) => insertQuickReply(replyId, ticket.id, i18n.language)}
                onClose={() => setPickerOpen(false)}
              />
            )}
          </div>

          <textarea
            ref={textareaRef}
            value={body}
            onChange={(event) => setBody(event.target.value)}
            rows={4}
            placeholder={t('tickets.composer.body_placeholder')}
            className="w-full rounded border border-gray-300 p-2 text-sm"
          />

          <AttachmentUploader attachments={attachments} onChange={setAttachments} />

          {send.fieldErrors.body && (
            <p className="mt-1 text-xs text-red-600">{send.fieldErrors.body.join(' ')}</p>
          )}

          {mentionsAllowed && (
            <div className="mt-2">
              <label className="mb-1 block text-xs font-medium text-gray-600">
                {t('tickets.composer.mentions_label')}
              </label>
              <input
                value={mentionsInput}
                onChange={(event) => setMentionsInput(event.target.value)}
                placeholder={t('tickets.composer.mentions_placeholder')}
                className="w-full rounded border border-gray-300 p-1.5 text-xs"
              />
              {mentionError && <p className="mt-1 text-xs text-red-600">{mentionError}</p>}
            </div>
          )}

          <div className="mt-2 flex items-center justify-between">
            <p className="text-xs text-gray-400">
              {mode === null ? t('tickets.composer.choose_mode_hint') : null}
            </p>
            <button
              type="submit"
              disabled={!canSubmit}
              className={`rounded px-4 py-1.5 text-sm font-medium text-white disabled:opacity-50 ${
                mode === 'internal' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700'
              }`}
            >
              {mode === 'internal' ? t('tickets.composer.submit_internal') : t('tickets.composer.submit_public')}
            </button>
          </div>
        </form>
      </section>
    </ActionGuard>
  );
}
