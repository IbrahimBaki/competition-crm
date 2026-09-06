import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { useGetCustomerTimeline } from '@/api/generated/customers/customers';
import type { GetCustomerTimelineParams } from '@/api/generated/model/getCustomerTimelineParams';
import { toTimelineEntry } from '../api/wire';
import type { TimelineEntry } from '../types';
import styles from './CustomerRecordV2.module.css';

const PAGE_SIZE = 25;

interface CustomerTimelineProps {
  customerUuid: string;
}

/**
 * CustomerTimelineController::index() paginates by `limit`/`before` (an
 * ISO-8601 cursor), NOT page/per_page — see .squad/gaps/34-481.md #8. The
 * shared `apiRequest` mutator's envelope auto-detection treats any response
 * without `meta.page` as a plain (non-paginated) success and discards
 * `meta` entirely, so `meta.next_before` is unreachable here. Instead this
 * requests `limit: PAGE_SIZE + 1` and, when more than PAGE_SIZE come back,
 * treats the PAGE_SIZE-th entry's `occurredAt` as the next cursor — this is
 * not a heuristic, it's exactly the overflow trick
 * BuildCustomerTimeline::execute() itself uses server-side to compute
 * `next_before`, just re-derived from data actually reachable client-side.
 */
export function CustomerTimeline({ customerUuid }: CustomerTimelineProps) {
  const { t, i18n } = useTranslation();
  const [entries, setEntries] = useState<TimelineEntry[]>([]);
  const [cursor, setCursor] = useState<string | undefined>(undefined);
  const [hasMore, setHasMore] = useState(true);
  const lastMergedCursorKeyRef = useRef<string>('');

  const dateFormatter = new Intl.DateTimeFormat(i18n.language, { dateStyle: 'medium' });
  const timeFormatter = new Intl.DateTimeFormat(i18n.language, { timeStyle: 'short' });

  // Keyed by customer id: navigating away and back must not show stale
  // entries from a previously-viewed customer.
  useEffect(() => {
    setEntries([]);
    setCursor(undefined);
    setHasMore(true);
    lastMergedCursorKeyRef.current = '';
  }, [customerUuid]);

  const params = { limit: PAGE_SIZE + 1, before: cursor } as unknown as GetCustomerTimelineParams;
  const query = useGetCustomerTimeline(customerUuid, params, {
    query: { refetchOnWindowFocus: false },
  }) as unknown as { data?: unknown[]; isLoading: boolean; isFetching: boolean; isError: boolean };

  useEffect(() => {
    if (!query.data) return;
    const cursorKey = cursor ?? '__first__';
    if (lastMergedCursorKeyRef.current === cursorKey) return;
    lastMergedCursorKeyRef.current = cursorKey;

    const mapped = query.data.map(toTimelineEntry);
    const hasNext = mapped.length > PAGE_SIZE;
    const pageEntries = hasNext ? mapped.slice(0, PAGE_SIZE) : mapped;

    setEntries((prev) => (cursor === undefined ? pageEntries : [...prev, ...pageEntries]));
    setHasMore(hasNext);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [query.data]);

  const loadMore = () => {
    const last = entries[entries.length - 1];
    if (!last) return;
    setCursor(last.occurredAt);
  };

  if (query.isLoading && entries.length === 0) {
    return <p className={styles.empty}>{t('customers.timeline.loading')}</p>;
  }

  if (query.isError && entries.length === 0) {
    return <p className={styles.error}>{t('customers.timeline.error')}</p>;
  }

  if (entries.length === 0) {
    return <p className={styles.empty}>{t('customers.timeline.empty')}</p>;
  }

  let lastDayKey: string | null = null;

  return (
    <div>
      <ul className={styles.list}>
        {entries.map((entry) => {
          const occurred = new Date(entry.occurredAt);
          const dayKey = occurred.toDateString();
          const showDaySeparator = dayKey !== lastDayKey;
          lastDayKey = dayKey;

          const labelKey = `customers.timeline.entry.${entry.source}.${entry.type}`;
          const label = t(labelKey, { defaultValue: t('customers.timeline.entry.unknown') });
          const ticketId =
            typeof entry.payload?.ticket_id === 'string'
              ? entry.payload.ticket_id
              : typeof entry.payload?.ticket_uuid === 'string'
                ? entry.payload.ticket_uuid
                : null;

          return (
            <li key={entry.id}>
              {showDaySeparator && (
                <div className={styles.day}>
                  {dateFormatter.format(occurred)}
                </div>
              )}
              <div className={`${styles.timelineItem} ${styles.row}`}>
                <span className={styles.type} aria-hidden="true">
                  •
                </span>
                <div className={styles.timelineContent}>
                  <p className={styles.value}>
                    {label}
                    {ticketId && (
                      <Link to={`/tickets/${ticketId}`} className={styles.linkButton}>
                        {t('customers.timeline.view_ticket')}
                      </Link>
                    )}
                  </p>
                  {typeof entry.payload?.body === 'string' && (
                    <p className={styles.meta}>{entry.payload.body}</p>
                  )}
                </div>
                <time className={`${styles.time} ds-bidi-value`}>{timeFormatter.format(occurred)}</time>
              </div>
            </li>
          );
        })}
      </ul>

      {hasMore && (
        <button
          type="button"
          onClick={loadMore}
          disabled={query.isFetching}
          className={styles.button}
        >
          {query.isFetching ? t('customers.timeline.loading_more') : t('customers.timeline.load_more')}
        </button>
      )}
    </div>
  );
}
