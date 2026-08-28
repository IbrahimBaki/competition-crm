import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { useAgentTaskListQuery } from '@/features/workspace/tasks/useAgentTaskListQuery';
import { TaskListTable } from '@/features/workspace/tasks/TaskListTable';
import { TaskFormDialog } from '@/features/workspace/tasks/TaskFormDialog';
import type { AgentTask } from '@/features/workspace/types';

interface TicketTasksPanelProps {
  ticket: { uuid: string; reference: string };
}

export function TicketTasksPanel({ ticket }: TicketTasksPanelProps) {
  const { t } = useTranslation();
  const [formOpen, setFormOpen] = useState(false);

  const query = useAgentTaskListQuery({ ticketId: ticket.uuid, sort: '-due_at' });

  return (
    <section className="rounded border border-gray-200 p-4">
      <div className="mb-3 flex items-center justify-between">
        <h2 className="text-sm font-semibold text-gray-700">{t('workspace.ticket_tasks.heading')}</h2>
        <button
          type="button"
          onClick={() => setFormOpen(true)}
          className="rounded bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-700"
        >
          {t('workspace.ticket_tasks.add')}
        </button>
      </div>

      <AsyncBoundary
        query={query}
        isEmpty={(data) => data.items.length === 0}
        empty={<EmptyState title={t('workspace.ticket_tasks.empty')} />}
      >
        {(data) => <TaskListTable tasks={data.items as AgentTask[]} />}
      </AsyncBoundary>

      <TaskFormDialog open={formOpen} onClose={() => setFormOpen(false)} ticket={ticket} />
    </section>
  );
}
