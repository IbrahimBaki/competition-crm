import { useMemo } from 'react';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetNotifications } from '@/api/generated/notifications/notifications';
import type { ApiPage } from '@/api/http/envelope';
import type { WorkspaceNotification } from '../types';
import { mapNotification } from './wire';

export function useNotificationsQuery(perPage = 10, enabled = true) {
  const rawQuery = useGetNotifications(
    { per_page: perPage } as never,
    { query: { refetchInterval: 30000, enabled } }
  ) as unknown as UseQueryResult<ApiPage<unknown>, unknown>;

  const query = useMemo(() => {
    if (!rawQuery.data) return rawQuery as unknown as UseQueryResult<ApiPage<WorkspaceNotification>, unknown>;
    return {
      ...rawQuery,
      data: { items: rawQuery.data.items.map(mapNotification), meta: rawQuery.data.meta },
    } as unknown as UseQueryResult<ApiPage<WorkspaceNotification>, unknown>;
  }, [rawQuery]);

  const unreadCount = useMemo(
    () => query.data?.items.filter((n) => !n.readAt).length ?? 0,
    [query.data]
  );

  return { query, unreadCount };
}
