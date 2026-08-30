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
    <section className="rounded border border-gray-200 p-4">
      <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('customers.detail.identity_heading')}</h2>

      <AsyncBoundary
        query={query}
        isEmpty={(page) => page.items.length === 0}
        empty={<p className="text-sm text-gray-400">{t('customers.detail.no_contacts')}</p>}
      >
        {(page) => {
          const contacts: CustomerContact[] = page.items.map(toCustomerContact);
          return (
            <ul className="flex flex-col gap-2">
              {contacts.map((contact) => (
                <li key={contact.uuid} className="flex items-center justify-between rounded border border-gray-100 px-3 py-2 text-sm">
                  <div>
                    <span className="font-medium text-gray-800">{t(`customers.contact_type.${contact.type}`, { defaultValue: contact.type })}</span>
                    <span className="ms-2 text-gray-700">{contact.value}</span>
                    {contact.isPrimary && (
                      <span className="ms-2 rounded bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-700">
                        {t('customers.detail.primary_contact')}
                      </span>
                    )}
                  </div>
                  <ActionGuard permission={PERMISSIONS.CUSTOMERS_CONTACT_MANAGE}>
                    <button
                      type="button"
                      disabled={removeMutation.isPending}
                      onClick={() => removeMutation.mutate({ customer: customerUuid, contact: contact.uuid })}
                      className="text-xs text-red-600 hover:underline disabled:opacity-50"
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

      {error && <p className="mt-3 text-sm text-red-600">{error}</p>}

      <ActionGuard permission={PERMISSIONS.CUSTOMERS_CONTACT_MANAGE}>
        <div className="mt-4 flex flex-wrap items-end gap-2 border-t border-gray-100 pt-3">
          <div className="flex flex-col">
            <label htmlFor="contact-type" className="text-xs font-medium text-gray-600">
              {t('customers.detail.contact_type_label')}
            </label>
            <select
              id="contact-type"
              value={type}
              onChange={(event) => setType(event.target.value as (typeof CONTACT_TYPES)[number])}
              className="rounded border border-gray-300 px-2 py-1 text-sm"
            >
              {CONTACT_TYPES.map((option) => (
                <option key={option} value={option}>
                  {t(`customers.contact_type.${option}`, { defaultValue: option })}
                </option>
              ))}
            </select>
          </div>
          <div className="flex flex-col">
            <label htmlFor="contact-value" className="text-xs font-medium text-gray-600">
              {t('customers.detail.contact_value_label')}
            </label>
            <input
              id="contact-value"
              type="text"
              value={value}
              onChange={(event) => setValue(event.target.value)}
              className="rounded border border-gray-300 px-2 py-1 text-sm"
            />
          </div>
          <div className="flex flex-col">
            <label htmlFor="contact-label" className="text-xs font-medium text-gray-600">
              {t('customers.detail.contact_label_label')}
            </label>
            <input
              id="contact-label"
              type="text"
              value={label}
              onChange={(event) => setLabel(event.target.value)}
              className="rounded border border-gray-300 px-2 py-1 text-sm"
            />
          </div>
          <label className="flex items-center gap-1 pb-1.5 text-xs text-gray-600">
            <input type="checkbox" checked={isPrimary} onChange={(event) => setIsPrimary(event.target.checked)} />
            {t('customers.detail.contact_primary_label')}
          </label>
          <button
            type="button"
            onClick={handleAdd}
            disabled={addMutation.isPending || !value.trim()}
            className="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {t('customers.actions.add_contact')}
          </button>
        </div>
      </ActionGuard>
    </section>
  );
}
