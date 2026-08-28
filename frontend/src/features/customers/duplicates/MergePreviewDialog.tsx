import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  getGetCustomerContactsQueryKey,
  getGetCustomerNotesQueryKey,
  getGetCustomerAttachmentsQueryKey,
  getGetCustomerTimelineQueryKey,
  getGetCustomerQueryKey,
  getGetCustomerDuplicatesQueryKey,
} from '@/api/generated/customers/customers';
import { apiRequest } from '@/api/http/mutator';
import type { ApiPage } from '@/api/http/envelope';
import { normaliseApiError } from '@/api/http/errors';
import { useMergeCustomers } from '../api/wire';
import { ConfirmActionDialog } from '@/shared/confirm/ConfirmActionDialog';
import type { DuplicateCandidate } from '../types';

interface MoveCounts {
  contacts: number;
  notes: number;
  attachments: number;
  // No dedicated "customer events" endpoint exists — this is a best-effort
  // count derived from the timeline (capped at one 100-entry page; see the
  // `eventsIsAtLeast` flag). The four categories mirror exactly
  // app/Domains/Customers/Services/Merge/MergeRelationRegistry.php's four
  // registered relations (contacts, notes, attachments, events).
  events: number;
  eventsIsAtLeast: boolean;
}

function useMoveCounts(customerUuid: string | null) {
  return useQuery<MoveCounts, unknown>({
    queryKey: ['customers', customerUuid, 'merge-move-counts'],
    enabled: Boolean(customerUuid),
    queryFn: async () => {
      const [contacts, notes, attachments, timeline] = await Promise.all([
        apiRequest<ApiPage<unknown>>({
          url: `/customers/${customerUuid}/contacts`,
          method: 'GET',
          params: { per_page: 1 },
        }),
        apiRequest<ApiPage<unknown>>({
          url: `/customers/${customerUuid}/notes`,
          method: 'GET',
          params: { per_page: 1 },
        }),
        apiRequest<ApiPage<unknown>>({
          url: `/customers/${customerUuid}/attachments`,
          method: 'GET',
          params: { per_page: 1 },
        }),
        // The timeline endpoint has no `meta.page`, so `apiRequest` returns
        // the plain entries array (not an ApiPage) — see
        // CustomerTimeline.tsx's doc comment for the full explanation.
        apiRequest<Record<string, unknown>[]>({
          url: `/customers/${customerUuid}/timeline`,
          method: 'GET',
          params: { limit: 100 },
        }),
      ]);
      // meta.total is the real count regardless of the per_page:1 trick
      // above (page size doesn't affect the reported total).
      const total = (page: ApiPage<unknown>) => page.meta.total ?? 0;
      const events = timeline;
      return {
        contacts: total(contacts),
        notes: total(notes),
        attachments: total(attachments),
        events: events.filter((e) => e.source === 'customer_event').length,
        eventsIsAtLeast: events.length >= 100,
      };
    },
  });
}

interface MergePreviewDialogProps {
  candidate: DuplicateCandidate;
  currentCustomerUuid: string;
  onClose: () => void;
  onMerged: () => void;
}

export function MergePreviewDialog({ candidate, currentCustomerUuid, onClose, onMerged }: MergePreviewDialogProps) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [error, setError] = useState<string | undefined>();
  // Direction is not defaulted — the operator must explicitly choose which
  // side survives before the confirm button enables.
  const [survivorUuid, setSurvivorUuid] = useState<string | null>(null);

  const other =
    candidate.customer.uuid === currentCustomerUuid ? candidate.duplicateCustomer : candidate.customer;
  const current = { uuid: currentCustomerUuid, name: candidate.customer.uuid === currentCustomerUuid ? candidate.customer.name : candidate.duplicateCustomer.name };

  const absorbedUuid = survivorUuid ? (survivorUuid === current.uuid ? other.uuid : current.uuid) : null;
  const moveCountsQuery = useMoveCounts(absorbedUuid);

  const mergeMutation = useMergeCustomers({
    onSuccess: (result) => {
      queryClient.invalidateQueries({ queryKey: getGetCustomerQueryKey(result.uuid) });
      queryClient.invalidateQueries({ queryKey: getGetCustomerContactsQueryKey(result.uuid) });
      queryClient.invalidateQueries({ queryKey: getGetCustomerNotesQueryKey(result.uuid) });
      queryClient.invalidateQueries({ queryKey: getGetCustomerAttachmentsQueryKey(result.uuid) });
      queryClient.invalidateQueries({ queryKey: getGetCustomerTimelineQueryKey(result.uuid) });
      queryClient.invalidateQueries({ queryKey: getGetCustomerDuplicatesQueryKey() });
      onMerged();
      navigate(`/customers/${result.uuid}`);
    },
    onError: (err) => {
      // A 409 (concurrent merge, already merged, self-merge) closes the
      // dialog, surfaces the normalised error in the parent panel, and
      // refetches the duplicates list rather than leaving a stale row.
      queryClient.invalidateQueries({ queryKey: getGetCustomerDuplicatesQueryKey() });
      setError(normaliseApiError(err).message);
      onMerged();
    },
  });

  return (
    <ConfirmActionDialog
      titleKey="customers.merge.dialog_title"
      consequenceKey="customers.merge.not_undoable"
      confirmLabelKey="customers.merge.confirm"
      onConfirm={() => {
        if (!survivorUuid || !absorbedUuid) return;
        mergeMutation.mutate({ survivor: survivorUuid, absorbed: absorbedUuid });
      }}
      onCancel={onClose}
      confirmDisabled={!survivorUuid}
      isSubmitting={mergeMutation.isPending}
      error={error}
      destructive
    >
      <fieldset className="mb-3">
        <legend className="mb-1 text-xs font-medium text-gray-600">{t('customers.merge.choose_direction')}</legend>
        {[current, other].map((party) => (
          <label key={party.uuid} className="mb-1 flex items-center gap-2 text-sm">
            <input
              type="radio"
              name="merge-survivor"
              checked={survivorUuid === party.uuid}
              onChange={() => setSurvivorUuid(party.uuid)}
            />
            {t('customers.merge.survivor_option', { name: party.name })}
          </label>
        ))}
      </fieldset>

      {survivorUuid && absorbedUuid && (
        <div className="mb-3 rounded bg-gray-50 p-2 text-xs text-gray-700">
          <p className="mb-1 font-medium">
            {t('customers.merge.will_move_heading', {
              from: other.uuid === absorbedUuid ? other.name : current.name,
              to: other.uuid === absorbedUuid ? current.name : other.name,
            })}
          </p>
          {moveCountsQuery.data ? (
            <ul className="list-inside list-disc">
              <li>{t('customers.merge.count_contacts', { count: moveCountsQuery.data.contacts })}</li>
              <li>{t('customers.merge.count_notes', { count: moveCountsQuery.data.notes })}</li>
              <li>{t('customers.merge.count_attachments', { count: moveCountsQuery.data.attachments })}</li>
              <li>
                {moveCountsQuery.data.eventsIsAtLeast
                  ? t('customers.merge.count_events_at_least', { count: moveCountsQuery.data.events })
                  : t('customers.merge.count_events', { count: moveCountsQuery.data.events })}
              </li>
            </ul>
          ) : (
            <p>{t('customers.merge.counts_loading')}</p>
          )}
        </div>
      )}
    </ConfirmActionDialog>
  );
}
