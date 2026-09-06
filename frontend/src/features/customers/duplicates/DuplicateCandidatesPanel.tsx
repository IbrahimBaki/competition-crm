import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient, type UseQueryResult } from '@tanstack/react-query';
import {
  useGetCustomerDuplicates,
  getGetCustomerDuplicatesQueryKey,
} from '@/api/generated/customers/customers';
import type { GetCustomerDuplicatesParams } from '@/api/generated/model/getCustomerDuplicatesParams';
import type { ApiPage } from '@/api/http/envelope';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { normaliseApiError } from '@/api/http/errors';
import { toDuplicateCandidate, useDismissDuplicateCandidate } from '../api/wire';
import type { DuplicateCandidate } from '../types';
import { MergePreviewDialog } from './MergePreviewDialog';
import styles from '../detail/CustomerRecordV2.module.css';

interface DuplicateCandidatesPanelProps {
  customerUuid: string;
}

// GET /customers/duplicates is NOT scoped to a single customer — it lists
// every pending duplicate pair system-wide (CustomerDuplicateController
// has no {customer} route parameter). This panel fetches the pending
// candidates and filters client-side to the pairs that involve the
// customer currently being viewed.
export function DuplicateCandidatesPanel({ customerUuid }: DuplicateCandidatesPanelProps) {
  const { t } = useTranslation();
  const queryClient = useQueryClient();
  const [error, setError] = useState<string | undefined>();
  const [mergeTarget, setMergeTarget] = useState<DuplicateCandidate | null>(null);

  const params = {
    per_page: 100,
    filter: { status: { eq: 'pending' } },
  } as unknown as GetCustomerDuplicatesParams;

  const query = useGetCustomerDuplicates(params) as unknown as UseQueryResult<
    ApiPage<Record<string, unknown>>,
    unknown
  >;

  const invalidate = () => queryClient.invalidateQueries({ queryKey: getGetCustomerDuplicatesQueryKey(params) });

  const dismissMutation = useDismissDuplicateCandidate({
    onSuccess: () => {
      invalidate();
      setError(undefined);
    },
    onError: (err) => setError(normaliseApiError(err).message),
  });

  return (
    <section className={styles.section}>
      <h2 className={styles.heading}>{t('customers.detail.duplicates_heading')}</h2>

      {error && <p className={styles.error}>{error}</p>}

      <AsyncBoundary
        query={query}
        isEmpty={(page) => {
          const all = page.items.map(toDuplicateCandidate);
          return !all.some((c) => c.customer.uuid === customerUuid || c.duplicateCustomer.uuid === customerUuid);
        }}
        empty={<p className={styles.empty}>{t('customers.detail.no_duplicates')}</p>}
      >
        {(page) => {
          const candidates = page.items
            .map(toDuplicateCandidate)
            .filter((c) => c.customer.uuid === customerUuid || c.duplicateCustomer.uuid === customerUuid);

          return (
            <ul className={styles.list}>
              {candidates.map((candidate) => {
                const other =
                  candidate.customer.uuid === customerUuid ? candidate.duplicateCustomer : candidate.customer;
                return (
                  <li
                    key={candidate.uuid}
                    className={`${styles.item} ${styles.row}`}
                  >
                    <span className={styles.value}>{other.name}</span>
                    <div className={styles.actions}>
                      <ActionGuard permission={PERMISSIONS.CUSTOMERS_MERGE}>
                        <button
                          type="button"
                          onClick={() => setMergeTarget(candidate)}
                          className={styles.buttonPrimary}
                        >
                          {t('customers.actions.review_merge')}
                        </button>
                      </ActionGuard>
                      <ActionGuard permission={PERMISSIONS.CUSTOMERS_DUPLICATE_REVIEW}>
                        <button
                          type="button"
                          disabled={dismissMutation.isPending}
                          onClick={() => dismissMutation.mutate({ candidate: candidate.uuid })}
                          className={styles.button}
                        >
                          {t('customers.actions.dismiss_duplicate')}
                        </button>
                      </ActionGuard>
                    </div>
                  </li>
                );
              })}
            </ul>
          );
        }}
      </AsyncBoundary>

      {mergeTarget && (
        <MergePreviewDialog
          candidate={mergeTarget}
          currentCustomerUuid={customerUuid}
          onClose={() => setMergeTarget(null)}
          onMerged={() => {
            invalidate();
            setMergeTarget(null);
          }}
        />
      )}
    </section>
  );
}
