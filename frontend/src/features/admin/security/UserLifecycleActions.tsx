import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ConfirmActionDialog } from '@/shared/confirm/ConfirmActionDialog';
import { normaliseApiError } from '@/api/http/errors';
import { useAuth } from '@/auth/AuthProvider';
import { adminErrorMessageKey } from '../api/errorCodes';
import { useSetUserActive } from '../api/wire';
import type { AdminUser } from '../types';

interface UserLifecycleActionsProps {
  user: AdminUser;
}

// Deactivating yourself is disabled up front (a plain id comparison, not a
// role-name check). The last-administrator guard can only be detected
// server-side (`CannotDeactivateLastAdministratorException`,
// `cannot_deactivate_last_administrator`, 409) — see
// .squad/gaps/36-483.md — so it is handled as an inline error after the
// fact, same as every other guard-rail code.
export function UserLifecycleActions({ user }: UserLifecycleActionsProps) {
  const { t } = useTranslation();
  const { user: currentUser } = useAuth();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [error, setError] = useState<string | undefined>();

  const mutation = useSetUserActive({
    onSuccess: () => setDialogOpen(false),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const isSelf = currentUser?.id === user.id;

  if (user.status === 'deactivated') {
    return (
      <button
        type="button"
        onClick={() => mutation.mutate({ user: user.id, active: true })}
        disabled={mutation.isPending}
        className="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
      >
        {t('admin.security.user.activate')}
      </button>
    );
  }

  return (
    <>
      <button
        type="button"
        onClick={() => {
          setError(undefined);
          setDialogOpen(true);
        }}
        disabled={isSelf}
        title={isSelf ? (t('admin.security.user.cannot_deactivate_self') as string) : undefined}
        className="rounded border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50 disabled:opacity-50"
      >
        {t('admin.security.user.deactivate')}
      </button>

      {dialogOpen && (
        <ConfirmActionDialog
          titleKey="admin.security.user.deactivate_dialog_title"
          consequenceKey="admin.security.user.deactivate_consequence"
          confirmLabelKey="admin.security.user.deactivate"
          onConfirm={() => mutation.mutate({ user: user.id, active: false })}
          onCancel={() => setDialogOpen(false)}
          isSubmitting={mutation.isPending}
          error={error}
          destructive
        />
      )}
    </>
  );
}
