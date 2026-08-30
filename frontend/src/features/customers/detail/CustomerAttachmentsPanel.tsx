import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient, type UseQueryResult } from '@tanstack/react-query';
import { useGetCustomerAttachments, getGetCustomerAttachmentsQueryKey } from '@/api/generated/customers/customers';
import type { ApiPage } from '@/api/http/envelope';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { normaliseApiError } from '@/api/http/errors';
import { AttachmentUploader, type UploadedAttachment } from '@/shared/attachments/AttachmentUploader';
import { useLinkCustomerAttachment, useRemoveCustomerAttachment, toCustomerAttachment } from '../api/wire';
import type { CustomerAttachment } from '../types';

interface CustomerAttachmentsPanelProps {
  customerUuid: string;
}

// The download URL bypasses the JSON-envelope-aware `apiRequest` mutator on
// purpose: GET /attachments/{attachment} streams the raw file, it doesn't
// return an envelope, so parsing it as JSON would fail. A plain <a> link
// lets the browser handle the authenticated (cookie-based Sanctum session)
// request and the download itself.
function attachmentDownloadUrl(uuid: string): string {
  return `${import.meta.env.VITE_API_BASE_URL}${import.meta.env.VITE_API_PREFIX}/attachments/${uuid}`;
}

export function CustomerAttachmentsPanel({ customerUuid }: CustomerAttachmentsPanelProps) {
  const { t, i18n } = useTranslation();
  const queryClient = useQueryClient();
  const [error, setError] = useState<string | undefined>();
  const [pendingUploads, setPendingUploads] = useState<UploadedAttachment[]>([]);
  const dateFormatter = new Intl.DateTimeFormat(i18n.language, { dateStyle: 'medium' });

  const query = useGetCustomerAttachments(customerUuid) as unknown as UseQueryResult<
    ApiPage<Record<string, unknown>>,
    unknown
  >;

  const invalidate = () =>
    queryClient.invalidateQueries({ queryKey: getGetCustomerAttachmentsQueryKey(customerUuid) });

  const linkMutation = useLinkCustomerAttachment({
    onSuccess: (_data, variables) => {
      invalidate();
      setPendingUploads((prev) => prev.filter((item) => item.uuid !== variables.attachmentUuid));
      setError(undefined);
    },
    onError: (err) => setError(normaliseApiError(err).message),
  });

  const removeMutation = useRemoveCustomerAttachment({
    onSuccess: () => invalidate(),
    onError: (err) => setError(normaliseApiError(err).message),
  });

  // AttachmentUploader only performs step 1 (generic upload -> uuid). Once
  // it reports a newly-uploaded attachment, link it to this customer.
  const handleUploaderChange = (
    updater: UploadedAttachment[] | ((prev: UploadedAttachment[]) => UploadedAttachment[])
  ) => {
    setPendingUploads((prev) => {
      const next = typeof updater === 'function' ? updater(prev) : updater;
      const added = next.find((item) => !prev.some((existing) => existing.uuid === item.uuid));
      if (added) {
        linkMutation.mutate({ customer: customerUuid, attachmentUuid: added.uuid });
      }
      return next;
    });
  };

  return (
    <section className="rounded border border-gray-200 p-4">
      <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('customers.detail.attachments_heading')}</h2>

      <AsyncBoundary
        query={query}
        isEmpty={(page) => page.items.length === 0}
        empty={<p className="text-sm text-gray-400">{t('customers.detail.no_attachments')}</p>}
      >
        {(page) => {
          const attachments: CustomerAttachment[] = page.items.map(toCustomerAttachment);
          return (
            <ul className="flex flex-col gap-2">
              {attachments.map((attachment) => {
                const downloadable = attachment.scanState === 'clean';
                return (
                  <li
                    key={attachment.uuid}
                    className="flex items-center justify-between rounded border border-gray-100 px-3 py-2 text-sm"
                  >
                    <div>
                      {downloadable ? (
                        <a
                          href={attachmentDownloadUrl(attachment.uuid)}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="font-medium text-blue-600 hover:underline"
                        >
                          {attachment.originalName}
                        </a>
                      ) : (
                        <span
                          className="font-medium text-gray-400"
                          title={t(`customers.attachment_scan_state.${attachment.scanState}_tooltip`)}
                        >
                          {attachment.originalName}
                        </span>
                      )}
                      <span className="ms-2 text-xs text-gray-400">
                        {t(`customers.attachment_scan_state.${attachment.scanState}`)} ·{' '}
                        {dateFormatter.format(new Date(attachment.createdAt))}
                      </span>
                    </div>
                    <ActionGuard permission={PERMISSIONS.CUSTOMERS_ATTACHMENT_MANAGE}>
                      <button
                        type="button"
                        disabled={removeMutation.isPending}
                        onClick={() => removeMutation.mutate({ customer: customerUuid, attachment: attachment.uuid })}
                        className="text-xs text-red-600 hover:underline disabled:opacity-50"
                      >
                        {t('customers.actions.remove_attachment')}
                      </button>
                    </ActionGuard>
                  </li>
                );
              })}
            </ul>
          );
        }}
      </AsyncBoundary>

      {error && <p className="mt-3 text-sm text-red-600">{error}</p>}

      <ActionGuard permission={PERMISSIONS.CUSTOMERS_ATTACHMENT_MANAGE}>
        <div className="mt-4 border-t border-gray-100 pt-3">
          <AttachmentUploader attachments={pendingUploads} onChange={handleUploaderChange} />
        </div>
      </ActionGuard>
    </section>
  );
}
