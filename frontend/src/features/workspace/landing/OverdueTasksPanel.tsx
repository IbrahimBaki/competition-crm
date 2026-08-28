import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useAuth } from '@/auth/AuthProvider';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { useAgentTaskListQuery } from '../tasks/useAgentTaskListQuery';
import { TaskListTable } from '../tasks/TaskListTable';
import { TaskFormDialog } from '../tasks/TaskFormDialog';
import { WorkspacePanel } from './WorkspacePanel';
import type { AgentTask } from '../types';

export function OverdueTasksPanel() {
  const { t } = useTranslation();
  const { user } = useAuth();
  const [formOpen, setFormOpen] = useState(false);

  const query = useAgentTaskListQuery({ overdue: true, ownerId: user?.id, sort: 'due_at' });

  return (
    <WorkspacePanel title={t('workspace.panel.overdue_tasks.title')} count={query.data?.meta.total as number | undefined}>
      <div className="mb-2">
        <button
          type="button"
          onClick={() => setFormOpen(true)}
          className="rounded bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-700"
        >
          {t('workspace.task.action_add')}
        </button>
      </div>

      <AsyncBoundary
        query={query}
        isEmpty={(data) => data.items.length === 0}
        empty={<EmptyState title={t('workspace.panel.overdue_tasks.empty_title')} />}
      >
        {(data) => <TaskListTable tasks={data.items as AgentTask[]} showTicketReference />}
      </AsyncBoundary>

      <TaskFormDialog open={formOpen} onClose={() => setFormOpen(false)} />
    </WorkspacePanel>
  );
}
