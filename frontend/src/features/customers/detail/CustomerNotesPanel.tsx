import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient, type UseQueryResult } from '@tanstack/react-query';
import { useGetCustomerNotes, getGetCustomerNotesQueryKey } from '@/api/generated/customers/customers';
import type { ApiPage } from '@/api/http/envelope';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { normaliseApiError } from '@/api/http/errors';
import { useAddCustomerNote, useRemoveCustomerNote, toCustomerNote } from '../api/wire';
import type { CustomerNote } from '../types';
import styles from './CustomerRecordV2.module.css';

interface CustomerNotesPanelProps {
  customerUuid: string;
}

// There is no update-note endpoint (routes/api.php only has index/store/
// destroy for customers/{customer}/notes) — no edit affordance is rendered.
export function CustomerNotesPanel({ customerUuid }: CustomerNotesPanelProps) {
  const { t, i18n } = useTranslation();
  const queryClient = useQueryClient();
  const [body, setBody] = useState('');
  const [error, setError] = useState<string | undefined>();
  const dateFormatter = new Intl.DateTimeFormat(i18n.language, { dateStyle: 'medium', timeStyle: 'short' });

  const query = useGetCustomerNotes(customerUuid) as unknown as UseQueryResult<
    ApiPage<Record<string, unknown>>,
    unknown
  >;

  const invalidate = () => queryClient.invalidateQueries({ queryKey: getGetCustomerNotesQueryKey(customerUuid) });

  const addMutation = useAddCustomerNote({
    onSuccess: () => {
      invalidate();
      setBody('');
      setError(undefined);
    },
    onError: (err) => setError(normaliseApiError(err).message),
  });

  const removeMutation = useRemoveCustomerNote({
    onSuccess: () => invalidate(),
    onError: (err) => setError(normaliseApiError(err).message),
  });

  return (
    <section className={styles.section}>
      <h2 className={styles.heading}>{t('customers.detail.notes_heading')}</h2>

      <AsyncBoundary
        query={query}
        isEmpty={(page) => page.items.length === 0}
        empty={<p className={styles.empty}>{t('customers.detail.no_notes')}</p>}
      >
        {(page) => {
          const notes: CustomerNote[] = page.items.map(toCustomerNote);
          return (
            <ul className={styles.list}>
              {notes.map((note) => (
                <li key={note.uuid} className={styles.item}>
                  <div className={styles.row}>
                    <p className={styles.note}>{note.body}</p>
                    <ActionGuard permission={PERMISSIONS.CUSTOMERS_NOTE_DELETE}>
                      <button
                        type="button"
                        disabled={removeMutation.isPending}
                        onClick={() => removeMutation.mutate({ customer: customerUuid, note: note.uuid })}
                        className={styles.linkButton}
                      >
                        {t('customers.actions.remove_note')}
                      </button>
                    </ActionGuard>
                  </div>
                  <p className={styles.meta}>
                    {note.authorName ?? t('customers.detail.unknown_author')} ·{' '}
                    {dateFormatter.format(new Date(note.createdAt))}
                  </p>
                </li>
              ))}
            </ul>
          );
        }}
      </AsyncBoundary>

      {error && <p className={styles.error}>{error}</p>}

      <ActionGuard permission={PERMISSIONS.CUSTOMERS_NOTE_CREATE}>
        <div className={styles.form}>
          <label htmlFor="new-note-body" className={styles.label}>
            {t('customers.detail.new_note_label')}
          </label>
          <textarea
            id="new-note-body"
            value={body}
            onChange={(event) => setBody(event.target.value)}
            rows={3}
            className={`${styles.control} ${styles.textarea}`}
          />
          <button
            type="button"
            onClick={() => {
              if (!body.trim()) return;
              setError(undefined);
              addMutation.mutate({ customer: customerUuid, body: body.trim() });
            }}
            disabled={addMutation.isPending || !body.trim()}
            className={styles.buttonPrimary}
          >
            {t('customers.actions.add_note')}
          </button>
        </div>
      </ActionGuard>
    </section>
  );
}
