import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient } from '@tanstack/react-query';
import { useGetTicketStatuses } from '@/api/generated/ticketing/ticketing';
import type { ApiPage } from '@/api/http/envelope';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { pickBilingual } from '../utils/bilingual';
import { changeTicketStatus, assignTicket } from '../api/wire';
import { runWithConcurrency, type ConcurrencyResult } from './runWithConcurrency';
import type { TicketStatusSummary } from '../types';

const MAX_CONCURRENCY = 4;

type BulkAction = 'assign' | 'status';

interface TicketBulkActionsProps {
  selected: string[];
  onCleared: () => void;
}

// There is no bulk-mutation endpoint on the backend (grep of routes/api.php
// confirms no `bulk` route under Ticketing). This fans out to the per-ticket
// endpoints with bounded concurrency instead. "Bulk tag" from the story plan
// is intentionally omitted: no tag-assignment endpoint exists at all (no
// `reclassify`/tag route in routes/api.php), so there's nothing to fan out to.
export function TicketBulkActions({ selected, onCleared }: TicketBulkActionsProps) {
  const { t, i18n } = useTranslation();
  const queryClient = useQueryClient();
  const [action, setAction] = useState<BulkAction>('assign');
  const [assigneeUuid, setAssigneeUuid] = useState('');
  const [statusId, setStatusId] = useState('');
  const [isRunning, setIsRunning] = useState(false);
  const [progress, setProgress] = useState<{ completed: number; total: number } | null>(null);
  const [results, setResults] = useState<ConcurrencyResult<string>[] | null>(null);

  const statusesQuery = useGetTicketStatuses(
    { per_page: 100 },
    { query: { enabled: action === 'status' } }
  ) as unknown as { data?: ApiPage<TicketStatusSummary> };

  if (selected.length === 0) return null;

  const canRun = action === 'assign' ? assigneeUuid.trim().length > 0 : statusId.length > 0;

  const handleRun = async () => {
    setIsRunning(true);
    setResults(null);
    setProgress({ completed: 0, total: selected.length });

    const worker = async (ticketUuid: string) => {
      if (action === 'assign') {
        await assignTicket(ticketUuid, assigneeUuid.trim());
      } else {
        await changeTicketStatus(ticketUuid, statusId);
      }
    };

    const outcome = await runWithConcurrency(selected, worker, MAX_CONCURRENCY, (completed, total) =>
      setProgress({ completed, total })
    );

    setResults(outcome);
    setIsRunning(false);
    // Invalidate once, after the whole batch — not per item. Matches the
    // list endpoint (`/tickets`) and both queue endpoints (`/tickets/queues/...`).
    queryClient.invalidateQueries({
      predicate: (q) => typeof q.queryKey[0] === 'string' && q.queryKey[0].startsWith('/tickets'),
    });
  };

  const failures = results?.filter((result) => !result.ok) ?? [];

  return (
    <ActionGuard anyPermission={[PERMISSIONS.TICKETS_ASSIGN, PERMISSIONS.TICKETS_STATUS_CHANGE]}>
      <div className="flex flex-col gap-3 rounded border border-blue-200 bg-blue-50 p-3">
        <div className="flex flex-wrap items-center gap-3">
          <span className="text-sm font-medium text-blue-900">
            {t('tickets.bulk.selected_count', { count: selected.length })}
          </span>

          <select
            value={action}
            onChange={(event) => setAction(event.target.value as BulkAction)}
            className="rounded border border-gray-300 px-2 py-1 text-sm"
            disabled={isRunning}
          >
            <ActionGuard permission={PERMISSIONS.TICKETS_ASSIGN}>
              <option value="assign">{t('tickets.bulk.action_assign')}</option>
            </ActionGuard>
            <ActionGuard permission={PERMISSIONS.TICKETS_STATUS_CHANGE}>
              <option value="status">{t('tickets.bulk.action_status')}</option>
            </ActionGuard>
          </select>

          {action === 'assign' && (
            <input
              type="text"
              value={assigneeUuid}
              onChange={(event) => setAssigneeUuid(event.target.value)}
              placeholder={t('tickets.filters.uuid_placeholder')}
              className="w-56 rounded border border-gray-300 px-2 py-1 text-sm"
              disabled={isRunning}
            />
          )}

          {action === 'status' && (
            <select
              value={statusId}
              onChange={(event) => setStatusId(event.target.value)}
              className="rounded border border-gray-300 px-2 py-1 text-sm"
              disabled={isRunning}
            >
              <option value="">{t('tickets.bulk.choose_status')}</option>
              {(statusesQuery.data?.items ?? []).map((status) => (
                <option key={status.uuid} value={status.uuid}>
                  {pickBilingual(status.name, i18n.language)}
                </option>
              ))}
            </select>
          )}

          <button
            type="button"
            onClick={handleRun}
            disabled={!canRun || isRunning}
            className="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {t('tickets.bulk.apply')}
          </button>

          <button
            type="button"
            onClick={onCleared}
            disabled={isRunning}
            className="text-sm text-gray-600 hover:underline"
          >
            {t('tickets.bulk.clear_selection')}
          </button>
        </div>

        {progress && isRunning && (
          <p className="text-sm text-blue-800">
            {t('tickets.bulk.progress', { completed: progress.completed, total: progress.total })}
          </p>
        )}

        {results && (
          <div className="text-sm">
            <p className="text-gray-700">
              {t('tickets.bulk.result_summary', {
                succeeded: results.length - failures.length,
                total: results.length,
              })}
            </p>
            {failures.length > 0 && (
              <ul className="mt-1 list-inside list-disc text-red-700">
                {failures.map((failure) => (
                  <li key={failure.item}>
                    {failure.item}: {failure.error}
                  </li>
                ))}
              </ul>
            )}
          </div>
        )}
      </div>
    </ActionGuard>
  );
}
