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
    <section className="rounded border border-gray-200 p-4">
      <h2 className="mb-3 text-sm font-semibold text-gray-700">{t('customers.detail.notes_heading')}</h2>

      <AsyncBoundary
        query={query}
        isEmpty={(page) => page.items.length === 0}
        empty={<p className="text-sm text-gray-400">{t('customers.detail.no_notes')}</p>}
      >
        {(page) => {
          const notes: CustomerNote[] = page.items.map(toCustomerNote);
          return (
            <ul className="flex flex-col gap-2">
              {notes.map((note) => (
                <li key={note.uuid} className="rounded border border-gray-100 px-3 py-2 text-sm">
                  <div className="flex items-start justify-between gap-2">
                    <p className="whitespace-pre-wrap text-gray-800">{note.body}</p>
                    <ActionGuard permission={PERMISSIONS.CUSTOMERS_NOTE_DELETE}>
                      <button
                        type="button"
                        disabled={removeMutation.isPending}
                        onClick={() => removeMutation.mutate({ customer: customerUuid, note: note.uuid })}
                        className="shrink-0 text-xs text-red-600 hover:underline disabled:opacity-50"
                      >
                        {t('customers.actions.remove_note')}
                      </button>
                    </ActionGuard>
                  </div>
                  <p className="mt-1 text-xs text-gray-400">
                    {note.authorName ?? t('customers.detail.unknown_author')} ·{' '}
                    {dateFormatter.format(new Date(note.createdAt))}
                  </p>
                </li>
              ))}
            </ul>
          );
        }}
      </AsyncBoundary>

      {error && <p className="mt-3 text-sm text-red-600">{error}</p>}

      <ActionGuard permission={PERMISSIONS.CUSTOMERS_NOTE_CREATE}>
        <div className="mt-4 border-t border-gray-100 pt-3">
          <label htmlFor="new-note-body" className="mb-1 block text-xs font-medium text-gray-600">
            {t('customers.detail.new_note_label')}
          </label>
          <textarea
            id="new-note-body"
            value={body}
            onChange={(event) => setBody(event.target.value)}
            rows={3}
            className="w-full rounded border border-gray-300 px-2 py-1 text-sm"
          />
          <button
            type="button"
            onClick={() => {
              if (!body.trim()) return;
              setError(undefined);
              addMutation.mutate({ customer: customerUuid, body: body.trim() });
            }}
            disabled={addMutation.isPending || !body.trim()}
            className="mt-2 rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {t('customers.actions.add_note')}
          </button>
        </div>
      </ActionGuard>
    </section>
  );
}
