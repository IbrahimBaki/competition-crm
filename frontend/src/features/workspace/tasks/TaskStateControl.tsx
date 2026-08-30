import { useTranslation } from 'react-i18next';
import { useQueryClient } from '@tanstack/react-query';
import { getGetAgentTasksQueryKey } from '@/api/generated/workspace/workspace';
import { isApiError } from '@/api/http/errors';
import { useChangeAgentTaskState, newIdempotencyKey } from '../api/wire';
import type { AgentTask, AgentTaskState } from '../types';

const ALLOWED_TRANSITIONS: Record<AgentTaskState, AgentTaskState[]> = {
  open: ['in_progress', 'done', 'cancelled'],
  in_progress: ['open', 'done', 'cancelled'],
  done: [],
  cancelled: [],
};

interface TaskStateControlProps {
  task: AgentTask;
}

export function TaskStateControl({ task }: TaskStateControlProps) {
  const { t } = useTranslation();
  const queryClient = useQueryClient();

  const mutation = useChangeAgentTaskState({
    onSuccess: () => queryClient.invalidateQueries({ queryKey: getGetAgentTasksQueryKey() }),
    onError: (error) => {
      // 404: task was deleted/reassigned elsewhere — remove the ghost row.
      if (isApiError(error) && error.kind === 'not_found') {
        queryClient.invalidateQueries({ queryKey: getGetAgentTasksQueryKey() });
      }
    },
  });

  const allowed = ALLOWED_TRANSITIONS[task.state];
  const errorMessage =
    mutation.isError && isApiError(mutation.error) && mutation.error.code === 'agent_task.illegal_transition'
      ? t('workspace.task.error_illegal_transition')
      : mutation.isError
        ? t('workspace.task.error_generic')
        : null;

  const change = (state: AgentTaskState) => {
    mutation.mutate({ task: task.uuid, state, idempotencyKey: newIdempotencyKey() });
  };

  return (
    <div className="flex flex-col gap-1">
      <div className="flex gap-1">
        {allowed.includes('done') && (
          <button
            type="button"
            onClick={() => change('done')}
            disabled={mutation.isPending}
            className="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700 disabled:opacity-50"
          >
            {t('workspace.task.action_complete')}
          </button>
        )}
        {task.state !== 'open' && allowed.includes('open') && (
          <button
            type="button"
            onClick={() => change('open')}
            disabled={mutation.isPending}
            className="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 disabled:opacity-50"
          >
            {t('workspace.task.action_reopen')}
          </button>
        )}
        {allowed.includes('cancelled') && (
          <button
            type="button"
            onClick={() => change('cancelled')}
            disabled={mutation.isPending}
            className="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 disabled:opacity-50"
          >
            {t('workspace.task.action_cancel')}
          </button>
        )}
      </div>
      {errorMessage && <p className="text-xs text-red-600">{errorMessage}</p>}
    </div>
  );
}
