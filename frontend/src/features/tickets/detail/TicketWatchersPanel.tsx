import { useTranslation } from 'react-i18next';
import { useQueryClient, type UseQueryResult } from '@tanstack/react-query';
import { useGetTicketWatchers, getGetTicketWatchersQueryKey } from '@/api/generated/ticketing/ticketing';
import { useWatchTicket, useUnwatchTicket } from '../api/wire';
import { useAuth } from '@/auth/AuthProvider';
import { usePermissions } from '@/auth/usePermissions';
import { PERMISSIONS } from '@/auth/permissions';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import type { TicketWatchersResponse } from '../types';

interface TicketWatchersPanelProps {
  ticket: string;
}

export function TicketWatchersPanel({ ticket }: TicketWatchersPanelProps) {
  const { t } = useTranslation();
  const { user } = useAuth();
  const { can } = usePermissions();
  const queryClient = useQueryClient();

  const query = useGetTicketWatchers(ticket) as unknown as UseQueryResult<TicketWatchersResponse, unknown>;

  const invalidate = () =>
    queryClient.invalidateQueries({ queryKey: getGetTicketWatchersQueryKey(ticket) });

  // POST watchers is idempotent server-side (a duplicate add is treated as
  // success) — invalidate/refetch on success either way, never surface a
  // conflict as an error.
  const watch = useWatchTicket({ onSuccess: invalidate, onError: invalidate });
  const unwatch = useUnwatchTicket({ onSuccess: invalidate });

  if (!can(PERMISSIONS.WORKSPACE_TICKET_WATCHERS_VIEW)) {
    return null;
  }

  const isWatching = query.data?.watchers.some((watcher) => watcher.uuid === user?.id) ?? false;

  return (
    <section className="rounded border border-gray-200 p-4">
      <div className="mb-3 flex items-center justify-between">
        <h2 className="text-sm font-semibold text-gray-700">{t('tickets.watchers.heading')}</h2>
        <button
          type="button"
          onClick={() => (isWatching ? unwatch.mutate({ ticket, userId: user?.id ?? '' }) : watch.mutate({ ticket }))}
          disabled={watch.isPending || unwatch.isPending}
          className="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 disabled:opacity-50"
        >
          {isWatching ? t('tickets.detail.unwatch') : t('tickets.detail.watch')}
        </button>
      </div>

      <AsyncBoundary
        query={query}
        isEmpty={(data) => data.watchers.length === 0}
        empty={<EmptyState title={t('tickets.watchers.empty')} />}
      >
        {(data) => (
          <ul className="space-y-1">
            {data.watchers.map((watcher) => (
              <li key={watcher.uuid} className="flex items-center justify-between text-sm">
                <span>{watcher.name}</span>
                <button
                  type="button"
                  onClick={() => unwatch.mutate({ ticket, userId: watcher.uuid })}
                  className="text-xs text-gray-400 hover:text-red-600"
                >
                  {t('tickets.watchers.remove')}
                </button>
              </li>
            ))}
          </ul>
        )}
      </AsyncBoundary>
    </section>
  );
}
