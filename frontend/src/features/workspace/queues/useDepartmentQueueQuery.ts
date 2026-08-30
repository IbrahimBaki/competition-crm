import { useMemo } from 'react';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetTicketQueueDepartment } from '@/api/generated/ticketing/ticketing';
import type { ApiPage } from '@/api/http/envelope';
import type { TicketListRow } from '../../tickets/types';
import { mapQueueTicket, toQueueListParams } from './wire';

/**
 * Dashboard-panel query for the signed-in user's department queue. Pass an
 * empty/undefined departmentId when the user has no department — the query
 * stays disabled and no request is made (per the plan's edge case: never
 * issue a request with an empty uuid path segment).
 */
export function useDepartmentQueueQuery(departmentId: string | undefined, perPage = 10) {
  const params = useMemo(() => toQueueListParams({ perPage, sort: '-updated_at' }), [perPage]);

  const rawQuery = useGetTicketQueueDepartment(departmentId ?? '', params as never, {
    query: { enabled: !!departmentId },
  }) as unknown as UseQueryResult<ApiPage<unknown>, unknown>;

  return useMemo(() => {
    if (!rawQuery.data) return rawQuery as unknown as UseQueryResult<ApiPage<TicketListRow>, unknown>;
    return {
      ...rawQuery,
      data: { items: rawQuery.data.items.map(mapQueueTicket), meta: rawQuery.data.meta },
    } as unknown as UseQueryResult<ApiPage<TicketListRow>, unknown>;
  }, [rawQuery]);
}
