import { useQuery } from '@tanstack/react-query';
import { getGetAgentTasksQueryKey } from '@/api/generated/workspace/workspace';
import { fetchAgentTasks, type AgentTaskListParams } from '../api/wire';

/**
 * Two call sites: the overdue-tasks panel (own tasks, filter.overdue=1) and
 * a ticket-scoped list (filter.ticket_id). See fetchAgentTasks/wire.ts for
 * the real filter contract (state/owner_id/ticket_id/due_before/due_after/overdue).
 */
export function useAgentTaskListQuery(params: AgentTaskListParams, enabled = true) {
  return useQuery({
    queryKey: [...getGetAgentTasksQueryKey(), params],
    queryFn: () => fetchAgentTasks(params),
    enabled,
  });
}
