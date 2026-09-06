import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient } from '@tanstack/react-query';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { normaliseApiError } from '@/api/http/errors';
import { getGetCustomerQueryKey } from '@/api/generated/customers/customers';
import { useBlockCustomer, useUnblockCustomer } from '../api/wire';
import { ConfirmActionDialog } from '@/shared/confirm/ConfirmActionDialog';
import type { CustomerDetail } from '../types';
import styles from './CustomerRecordV2.module.css';

interface CustomerDangerActionsProps {
  customer: CustomerDetail;
}

// Anonymise is intentionally NOT implemented here: there is no backend
// endpoint that anonymises a Customer (the only erasure route operates on
// staff User accounts) — see .squad/gaps/34-481.md #4. Only block/unblock
// are wired.
export function CustomerDangerActions({ customer }: CustomerDangerActionsProps) {
  const { t } = useTranslation();
  const queryClient = useQueryClient();
  const [dialog, setDialog] = useState<'block' | 'unblock' | null>(null);
  const [reason, setReason] = useState('');
  const [error, setError] = useState<string | undefined>();

  const invalidate = () =>
    queryClient.invalidateQueries({ queryKey: getGetCustomerQueryKey(customer.uuid) });

  const blockMutation = useBlockCustomer({
    onSuccess: () => {
      invalidate();
      setDialog(null);
      setReason('');
    },
    onError: (err) => setError(normaliseApiError(err).message),
  });

  const unblockMutation = useUnblockCustomer({
    onSuccess: () => {
      invalidate();
      setDialog(null);
    },
    onError: (err) => setError(normaliseApiError(err).message),
  });

  const openDialog = (which: 'block' | 'unblock') => {
    setError(undefined);
    setReason('');
    setDialog(which);
  };

  const closeDialog = () => {
    setDialog(null);
    setError(undefined);
  };

  if (customer.status === 'anonymised') {
    return null;
  }

  return (
    <>
      <ActionGuard permission={PERMISSIONS.CUSTOMERS_BLOCK}>
        {customer.status === 'blocked' ? (
          <button
            type="button"
            onClick={() => openDialog('unblock')}
            className={styles.button}
          >
            {t('customers.actions.unblock')}
          </button>
        ) : (
          <button
            type="button"
            onClick={() => openDialog('block')}
            className={styles.buttonDanger}
          >
            {t('customers.actions.block')}
          </button>
        )}
      </ActionGuard>

      {dialog === 'block' && (
        <ConfirmActionDialog
          titleKey="customers.actions.block_dialog_title"
          consequenceKey="customers.actions.block_dialog_consequence"
          confirmLabelKey="customers.actions.block"
          onConfirm={() => blockMutation.mutate({ customer: customer.uuid, reason: reason.trim() })}
          onCancel={closeDialog}
          confirmDisabled={reason.trim().length === 0}
          isSubmitting={blockMutation.isPending}
          error={error}
          destructive
          presentationClassName={styles.dialog}
        >
          <label htmlFor="block-reason" className={styles.label}>
            {t('customers.actions.block_reason_label')}
          </label>
          <textarea
            id="block-reason"
            value={reason}
            onChange={(event) => setReason(event.target.value)}
            rows={3}
            className={`${styles.control} ${styles.textarea}`}
          />
        </ConfirmActionDialog>
      )}

      {dialog === 'unblock' && (
        <ConfirmActionDialog
          titleKey="customers.actions.unblock_dialog_title"
          consequenceKey="customers.actions.unblock_dialog_consequence"
          confirmLabelKey="customers.actions.unblock"
          onConfirm={() => unblockMutation.mutate({ customer: customer.uuid })}
          onCancel={closeDialog}
          isSubmitting={unblockMutation.isPending}
          error={error}
          presentationClassName={styles.dialog}
        />
      )}
    </>
  );
}
