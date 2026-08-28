import { useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { postAttachments } from '@/api/generated/attachments/attachments';
import { normaliseApiError } from '@/api/http/errors';
import type { PostAttachmentsBody } from '@/api/generated/model/postAttachmentsBody';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';

export interface UploadedAttachment {
  uuid: string;
  name: string;
  scanState: 'pending' | 'clean' | 'infected' | 'failed';
}

interface InFlightUpload {
  key: string;
  name: string;
  progress: number;
  error?: string;
}

interface AttachmentUploaderProps {
  attachments: UploadedAttachment[];
  onChange: (updater: UploadedAttachment[] | ((prev: UploadedAttachment[]) => UploadedAttachment[])) => void;
}

// There is no JSON endpoint to re-check an attachment's scan_state after
// upload — GET /attachments/{attachment} streams the file itself and throws
// while scanning is pending, it doesn't return status. So a "pending" result
// here has no way to become "clean" without a fresh upload; this component
// reflects that rather than polling something that doesn't exist.
//
// Shared between the ticket composer and the customer attachments panel:
// both link a generically-uploaded attachment (this component's job) to
// their own owner afterward via their own domain-specific endpoint.
export function AttachmentUploader({ attachments, onChange }: AttachmentUploaderProps) {
  const { t } = useTranslation();
  const inputRef = useRef<HTMLInputElement>(null);
  const [inFlight, setInFlight] = useState<InFlightUpload[]>([]);

  const handleFiles = (files: FileList | null) => {
    if (!files) return;

    Array.from(files).forEach(async (file) => {
      const key = `${file.name}-${file.size}-${Date.now()}`;
      setInFlight((prev) => [...prev, { key, name: file.name, progress: 0 }]);

      try {
        const response = await postAttachments({ file } as PostAttachmentsBody, {
          onUploadProgress: (event) => {
            const progress = event.total ? Math.round((event.loaded / event.total) * 100) : 0;
            setInFlight((prev) => prev.map((item) => (item.key === key ? { ...item, progress } : item)));
          },
        });
        const data = response as unknown as {
          uuid: string;
          original_name: string;
          scan_state: UploadedAttachment['scanState'];
        };
        onChange((prev) => [...prev, { uuid: data.uuid, name: data.original_name, scanState: data.scan_state }]);
        setInFlight((prev) => prev.filter((item) => item.key !== key));
      } catch (error) {
        const normalised = normaliseApiError(error);
        setInFlight((prev) => prev.map((item) => (item.key === key ? { ...item, error: normalised.message } : item)));
      }
    });

    if (inputRef.current) inputRef.current.value = '';
  };

  const removeAttachment = (uuid: string) => {
    onChange((prev) => prev.filter((attachment) => attachment.uuid !== uuid));
  };

  return (
    <div className="mt-2">
      <ActionGuard permission={PERMISSIONS.ATTACHMENTS_UPLOAD}>
        <input ref={inputRef} type="file" multiple onChange={(event) => handleFiles(event.target.files)} className="text-sm" />
      </ActionGuard>

      {inFlight.map((item) => (
        <div key={item.key} className="mt-1 text-xs">
          {item.error ? (
            <span className="text-red-600">
              {item.name}: {item.error}
            </span>
          ) : (
            <>
              <span className="text-gray-500">
                {t('attachments.uploader.uploading', { name: item.name })} ({item.progress}%)
              </span>
              <div className="mt-0.5 h-1.5 w-full max-w-xs rounded bg-gray-200">
                <div
                  className="h-1.5 rounded bg-blue-600 transition-all"
                  style={{ width: `${item.progress}%` }}
                />
              </div>
            </>
          )}
        </div>
      ))}

      {attachments.length > 0 && (
        <ul className="mt-2 flex flex-wrap gap-2">
          {attachments.map((attachment) => (
            <li
              key={attachment.uuid}
              className={`flex items-center gap-1 rounded border px-2 py-1 text-xs ${
                attachment.scanState === 'clean'
                  ? 'border-gray-200 bg-gray-50 text-gray-700'
                  : 'border-amber-300 bg-amber-50 text-amber-800'
              }`}
            >
              <span>
                {attachment.name} — {t(`attachments.uploader.scan_state.${attachment.scanState}`)}
              </span>
              <button
                type="button"
                onClick={() => removeAttachment(attachment.uuid)}
                aria-label={t('attachments.uploader.remove', { name: attachment.name })}
                className="text-gray-400 hover:text-red-600"
              >
                ×
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
