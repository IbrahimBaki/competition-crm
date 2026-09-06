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
import styles from './CustomerRecordV2.module.css';

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
    <section className={styles.section}>
      <h2 className={styles.heading}>{t('customers.detail.attachments_heading')}</h2>

      <AsyncBoundary
        query={query}
        isEmpty={(page) => page.items.length === 0}
        empty={<p className={styles.empty}>{t('customers.detail.no_attachments')}</p>}
      >
        {(page) => {
          const attachments: CustomerAttachment[] = page.items.map(toCustomerAttachment);
          return (
            <ul className={styles.list}>
              {attachments.map((attachment) => {
                const downloadable = attachment.scanState === 'clean';
                return (
                  <li
                    key={attachment.uuid}
                    className={`${styles.item} ${styles.row}`}
                  >
                    <div>
                      <div className={styles.value}>{downloadable ? (
                        <a
                          href={attachmentDownloadUrl(attachment.uuid)}
                          target="_blank"
                          rel="noopener noreferrer"
                          className={`${styles.attachmentName} ds-bidi-value`}
                        >
                          {attachment.originalName}
                        </a>
                      ) : (
                        <span
                          className={`${styles.attachmentName} ds-bidi-value`}
                          title={t(`customers.attachment_scan_state.${attachment.scanState}_tooltip`)}
                        >
                          {attachment.originalName}
                        </span>
                      )}
                      <span className={styles.meta}>
                        {t(`customers.attachment_scan_state.${attachment.scanState}`)} ·{' '}
                        {dateFormatter.format(new Date(attachment.createdAt))}
                      </span></div>
                    </div>
                    <ActionGuard permission={PERMISSIONS.CUSTOMERS_ATTACHMENT_MANAGE}>
                      <button
                        type="button"
                        disabled={removeMutation.isPending}
                        onClick={() => removeMutation.mutate({ customer: customerUuid, attachment: attachment.uuid })}
                        className={styles.linkButton}
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

      {error && <p className={styles.error}>{error}</p>}

      <ActionGuard permission={PERMISSIONS.CUSTOMERS_ATTACHMENT_MANAGE}>
        <div className={`${styles.form} ${styles.uploader}`}>
          <AttachmentUploader attachments={pendingUploads} onChange={handleUploaderChange} triggerClassName={styles.button} />
        </div>
      </ActionGuard>
    </section>
  );
}
