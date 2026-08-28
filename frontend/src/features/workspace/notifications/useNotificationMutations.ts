import { useQueryClient } from '@tanstack/react-query';
import { getGetNotificationsQueryKey } from '@/api/generated/notifications/notifications';
import {
  usePostNotificationRead,
  usePostNotificationsReadAll,
} from '@/api/generated/notifications/notifications';

// notifications/{notification}/read and notifications/read-all carry no
// `idempotency` middleware (confirmed in routes/api.php) — no Idempotency-Key
// header needed for either.
export function useNotificationMutations() {
  const queryClient = useQueryClient();
  const invalidate = () => queryClient.invalidateQueries({ queryKey: getGetNotificationsQueryKey() });

  const markRead = usePostNotificationRead({ mutation: { onSuccess: invalidate } });
  const markAllRead = usePostNotificationsReadAll({ mutation: { onSuccess: invalidate } });

  return {
    markRead: (notification: string) => markRead.mutate({ notification }),
    markAllRead: () => markAllRead.mutate(),
  };
}
