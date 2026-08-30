import { useMemo } from 'react';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetTicketQueueMine } from '@/api/generated/ticketing/ticketing';
import type { ApiPage } from '@/api/http/envelope';
import type { TicketListRow } from '../../tickets/types';
import { mapQueueTicket, toQueueListParams } from './wire';

/**
 * Dashboard-panel query for "tickets assigned to me" — not URL-driven (unlike
 * frontend/src/features/tickets/list/useTicketListQuery.ts's 'mine' mode),
 * since multiple workspace panels on one page cannot share the same URL
 * query-string keys.
 */
export function useMyQueueQuery(perPage = 10) {
  const params = useMemo(() => toQueueListParams({ perPage, sort: '-updated_at' }), [perPage]);

  const rawQuery = useGetTicketQueueMine(params as never) as unknown as UseQueryResult<
    ApiPage<unknown>,
    unknown
  >;

  return useMemo(() => {
    if (!rawQuery.data) return rawQuery as unknown as UseQueryResult<ApiPage<TicketListRow>, unknown>;
    return {
      ...rawQuery,
      data: { items: rawQuery.data.items.map(mapQueueTicket), meta: rawQuery.data.meta },
    } as unknown as UseQueryResult<ApiPage<TicketListRow>, unknown>;
  }, [rawQuery]);
}
