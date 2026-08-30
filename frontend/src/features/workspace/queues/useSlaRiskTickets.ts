import { useMemo } from 'react';
import { useMyQueueQuery } from './useMyQueueQuery';

/**
 * SLA-risk derivation is client-side sort/filter ONLY on server-provided SLA
 * fields — never Date arithmetic, never recomputed timing (see
 * frontend/src/__tests__/no-sla-recalculation.test.ts, and
 * .squad/gaps/35-482.md #18/#19).
 *
 * Confirmed live: tickets/queues/mine rows carry no `sla` field at all (they
 * are raw Eloquent rows, not TicketResource-wrapped — TicketResource's sla
 * block additionally requires the slaClocks relation, which isn't in this
 * endpoint's allowed includes either way). So this hook can only ever report
 * `available: false` today; the panel must render a translated "not
 * available" EmptyState rather than fabricate risk data.
 */
export function useSlaRiskTickets(perPage = 10) {
  const query = useMyQueueQuery(perPage);

  const available = useMemo(() => {
    const first = query.data?.items[0] as { sla?: unknown } | undefined;
    return first ? 'sla' in first : false;
  }, [query.data]);

  const items = useMemo(() => {
    if (!available || !query.data) return [];
    return (query.data.items as unknown as Array<{ sla?: { first_response?: unknown; resolution?: unknown } }>)
      .filter((row) => Boolean(row.sla?.first_response || row.sla?.resolution));
  }, [available, query.data]);

  return { query, available, items };
}
