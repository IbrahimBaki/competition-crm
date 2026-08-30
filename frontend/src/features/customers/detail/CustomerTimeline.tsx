import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { useGetCustomerTimeline } from '@/api/generated/customers/customers';
import type { GetCustomerTimelineParams } from '@/api/generated/model/getCustomerTimelineParams';
import { toTimelineEntry } from '../api/wire';
import type { TimelineEntry } from '../types';

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
    return <p className="text-sm text-gray-400">{t('customers.timeline.loading')}</p>;
  }

  if (query.isError && entries.length === 0) {
    return <p className="text-sm text-red-600">{t('customers.timeline.error')}</p>;
  }

  if (entries.length === 0) {
    return <p className="text-sm text-gray-400">{t('customers.timeline.empty')}</p>;
  }

  let lastDayKey: string | null = null;

  return (
    <div>
      <ul className="flex flex-col gap-1">
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
                <div className="mt-3 mb-1 text-xs font-semibold uppercase text-gray-400 first:mt-0">
                  {dateFormatter.format(occurred)}
                </div>
              )}
              <div className="flex items-start gap-2 rounded border border-gray-100 px-3 py-2 text-sm">
                <span className="mt-0.5 text-gray-400" aria-hidden="true">
                  •
                </span>
                <div className="flex-1">
                  <p className="text-gray-800">
                    {label}
                    {ticketId && (
                      <Link to={`/tickets/${ticketId}`} className="ms-2 text-xs text-blue-600 hover:underline">
                        {t('customers.timeline.view_ticket')}
                      </Link>
                    )}
                  </p>
                  {typeof entry.payload?.body === 'string' && (
                    <p className="mt-0.5 truncate text-xs text-gray-500">{entry.payload.body}</p>
                  )}
                </div>
                <span className="shrink-0 text-xs text-gray-400">{timeFormatter.format(occurred)}</span>
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
          className="mt-3 w-full rounded border border-gray-300 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
        >
          {query.isFetching ? t('customers.timeline.loading_more') : t('customers.timeline.load_more')}
        </button>
      )}
    </div>
  );
}
