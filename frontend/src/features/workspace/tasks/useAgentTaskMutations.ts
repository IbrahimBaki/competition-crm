import { useQueryClient } from '@tanstack/react-query';
import { getGetAgentTasksQueryKey } from '@/api/generated/workspace/workspace';
import {
  useCreateAgentTask,
  useUpdateAgentTask,
  useChangeAgentTaskState,
  newIdempotencyKey,
  type CreateTaskInput,
} from '../api/wire';
import type { AgentTaskState } from '../types';

/**
 * Wraps the four agent-task write operations with shared invalidation:
 * getGetAgentTasksQueryKey() always, plus the ticket-detail key when a
 * ticket-scoped task list needs to reflect the change too.
 */
export function useAgentTaskMutations(onSettledTicketId?: string) {
  const queryClient = useQueryClient();

  const invalidate = () => {
    queryClient.invalidateQueries({ queryKey: getGetAgentTasksQueryKey() });
    if (onSettledTicketId) {
      queryClient.invalidateQueries({ queryKey: ['/tickets', onSettledTicketId] });
    }
  };

  const create = useCreateAgentTask({ onSuccess: invalidate });
  const update = useUpdateAgentTask({ onSuccess: invalidate });
  const changeState = useChangeAgentTaskState({ onSuccess: invalidate });

  return {
    create: (input: CreateTaskInput) => create.mutate({ ...input, idempotencyKey: newIdempotencyKey() }),
    createState: create,
    update: (task: string, input: { title?: string; description?: string | null; dueAt?: string | null }) =>
      update.mutate({ task, input, idempotencyKey: newIdempotencyKey() }),
    updateState: update,
    changeState: (task: string, state: AgentTaskState) =>
      changeState.mutate({ task, state, idempotencyKey: newIdempotencyKey() }),
    changeStateState: changeState,
  };
}
