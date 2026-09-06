import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient, type UseQueryResult } from '@tanstack/react-query';
import { useGetCustomerContacts, getGetCustomerContactsQueryKey } from '@/api/generated/customers/customers';
import type { ApiPage } from '@/api/http/envelope';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { normaliseApiError } from '@/api/http/errors';
import { useAddCustomerContact, useRemoveCustomerContact, toCustomerContact } from '../api/wire';
import type { CustomerContact } from '../types';
import styles from './CustomerRecordV2.module.css';

// Mirrors app/Domains/Customers/Models/ContactType.php exactly.
const CONTACT_TYPES = ['email', 'phone', 'whatsapp', 'sms', 'chat', 'portal_login', 'web_form'] as const;

interface CustomerIdentityPanelProps {
  customerUuid: string;
}

export function CustomerIdentityPanel({ customerUuid }: CustomerIdentityPanelProps) {
  const { t } = useTranslation();
  const queryClient = useQueryClient();
  const [error, setError] = useState<string | undefined>();
  const [type, setType] = useState<(typeof CONTACT_TYPES)[number]>('email');
  const [value, setValue] = useState('');
  const [label, setLabel] = useState('');
  const [isPrimary, setIsPrimary] = useState(false);

  const query = useGetCustomerContacts(customerUuid) as unknown as UseQueryResult<
    ApiPage<Record<string, unknown>>,
    unknown
  >;

  const invalidate = () =>
    queryClient.invalidateQueries({ queryKey: getGetCustomerContactsQueryKey(customerUuid) });

  const addMutation = useAddCustomerContact({
    onSuccess: () => {
      invalidate();
      setValue('');
      setLabel('');
      setIsPrimary(false);
      setError(undefined);
    },
    onError: (err) => setError(normaliseApiError(err).message),
  });

  const removeMutation = useRemoveCustomerContact({
    onSuccess: () => {
      invalidate();
      setError(undefined);
    },
    // Removing the LAST contact is rejected by the backend
    // (CustomerMustHaveContactException) — surfaced here rather than
    // pre-empted client-side, since the "last contact" rule is the
    // server's to enforce (see .squad/gaps/34-481.md context on
    // CustomerContactPolicy/AddCustomerContact).
    onError: (err) => setError(normaliseApiError(err).message),
  });

  const handleAdd = () => {
    if (!value.trim()) return;
    setError(undefined);
    addMutation.mutate({ customer: customerUuid, type, value: value.trim(), label: label.trim() || undefined, isPrimary });
  };

  return (
    <section className={styles.section}>
      <h2 className={styles.heading}>{t('customers.detail.identity_heading')}</h2>

      <AsyncBoundary
        query={query}
        isEmpty={(page) => page.items.length === 0}
        empty={<p className={styles.empty}>{t('customers.detail.no_contacts')}</p>}
      >
        {(page) => {
          const contacts: CustomerContact[] = page.items.map(toCustomerContact);
          return (
            <ul className={styles.list}>
              {contacts.map((contact) => (
                <li key={contact.uuid} className={`${styles.item} ${styles.row}`}>
                  <div className={styles.value}>
                    <span className={styles.type}>{t(`customers.contact_type.${contact.type}`, { defaultValue: contact.type })}</span>{' '}
                    <span className="ds-bidi-value">{contact.value}</span>
                    {contact.isPrimary && (
                      <span className={styles.badge}>
                        {t('customers.detail.primary_contact')}
                      </span>
                    )}
                  </div>
                  <ActionGuard permission={PERMISSIONS.CUSTOMERS_CONTACT_MANAGE}>
                    <button
                      type="button"
                      disabled={removeMutation.isPending}
                      onClick={() => removeMutation.mutate({ customer: customerUuid, contact: contact.uuid })}
                      className={styles.linkButton}
                    >
                      {t('customers.actions.remove_contact')}
                    </button>
                  </ActionGuard>
                </li>
              ))}
            </ul>
          );
        }}
      </AsyncBoundary>

      {error && <p className={styles.error}>{error}</p>}

      <ActionGuard permission={PERMISSIONS.CUSTOMERS_CONTACT_MANAGE}>
        <div className={styles.form}>
          <div className={styles.formRow}>
          <div className={styles.field}>
            <label htmlFor="contact-type" className={styles.label}>
              {t('customers.detail.contact_type_label')}
            </label>
            <select
              id="contact-type"
              value={type}
              onChange={(event) => setType(event.target.value as (typeof CONTACT_TYPES)[number])}
              className={styles.control}
            >
              {CONTACT_TYPES.map((option) => (
                <option key={option} value={option}>
                  {t(`customers.contact_type.${option}`, { defaultValue: option })}
                </option>
              ))}
            </select>
          </div>
          <div className={styles.field}>
            <label htmlFor="contact-value" className={styles.label}>
              {t('customers.detail.contact_value_label')}
            </label>
            <input
              id="contact-value"
              type="text"
              value={value}
              onChange={(event) => setValue(event.target.value)}
              className={`${styles.control} ds-bidi-value`}
            />
          </div>
          <div className={styles.field}>
            <label htmlFor="contact-label" className={styles.label}>
              {t('customers.detail.contact_label_label')}
            </label>
            <input
              id="contact-label"
              type="text"
              value={label}
              onChange={(event) => setLabel(event.target.value)}
              className={styles.control}
            />
          </div>
          <label className={styles.label}>
            <input type="checkbox" checked={isPrimary} onChange={(event) => setIsPrimary(event.target.checked)} />
            {t('customers.detail.contact_primary_label')}
          </label>
          </div>
          <button
            type="button"
            onClick={handleAdd}
            disabled={addMutation.isPending || !value.trim()}
            className={styles.buttonPrimary}
          >
            {t('customers.actions.add_contact')}
          </button>
        </div>
      </ActionGuard>
    </section>
  );
}
