import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

interface ConfirmActionDialogProps {
  titleKey: string;
  /** i18n key for the sentence stating what this action will do. Required so a
   * destructive action can never be wired up without stating its consequence. */
  consequenceKey: string;
  children?: ReactNode;
  confirmLabelKey: string;
  onConfirm: () => void;
  onCancel: () => void;
  confirmDisabled?: boolean;
  isSubmitting?: boolean;
  error?: string;
  destructive?: boolean;
}

// Reusable confirmation dialog for destructive/consequential actions across
// the app (customer block/merge, admin deactivate/delete/replay). No
// dialog/modal library is used elsewhere in this codebase, so this is a
// plain accessible overlay.
export function ConfirmActionDialog({
  titleKey,
  consequenceKey,
  children,
  confirmLabelKey,
  onConfirm,
  onCancel,
  confirmDisabled = false,
  isSubmitting = false,
  error,
  destructive = false,
}: ConfirmActionDialogProps) {
  const { t } = useTranslation();

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-action-dialog-title"
        className="w-full max-w-md rounded bg-white p-5 shadow-lg"
      >
        <h2 id="confirm-action-dialog-title" className="mb-3 text-lg font-semibold text-gray-900">
          {t(titleKey)}
        </h2>

        <p className="mb-3 text-sm text-gray-700">{t(consequenceKey)}</p>

        {children && <div className="mb-4 text-sm text-gray-700">{children}</div>}

        {error && <p className="mb-3 text-sm text-red-600">{error}</p>}

        <div className="flex justify-end gap-2">
          <button
            type="button"
            onClick={onCancel}
            disabled={isSubmitting}
            className="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            {t('customers.actions.cancel')}
          </button>
          <button
            type="button"
            onClick={onConfirm}
            disabled={confirmDisabled || isSubmitting}
            className={`rounded px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50 ${
              destructive ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'
            }`}
          >
            {t(confirmLabelKey)}
          </button>
        </div>
      </div>
    </div>
  );
}
