import { useTranslation } from 'react-i18next';
import type { AgentTask } from '../types';
import { TaskStateControl } from './TaskStateControl';
import { RescheduleTaskControl } from './RescheduleTaskControl';

interface TaskListTableProps {
  tasks: AgentTask[];
  showTicketReference?: boolean;
}

export function TaskListTable({ tasks, showTicketReference = false }: TaskListTableProps) {
  const { t } = useTranslation();

  return (
    <ul className="divide-y divide-gray-100">
      {tasks.map((task) => (
        <li key={task.uuid} className="flex flex-col gap-1 py-2 text-sm">
          <div className="flex items-center justify-between gap-2">
            <span className="truncate font-medium text-gray-800" title={task.title}>
              {task.title}
            </span>
            {task.isOverdue && (
              <span className="shrink-0 rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                {t('workspace.task.overdue_badge')}
              </span>
            )}
          </div>
          <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
            {task.owner.name && <span>{t('workspace.task.owner_label', { name: task.owner.name })}</span>}
            {task.dueAt && <span>{t('workspace.task.due_label', { date: new Date(task.dueAt).toLocaleString() })}</span>}
            {showTicketReference && task.ticket && (
              <span className="font-mono">{task.ticket.reference}</span>
            )}
          </div>
          <div className="flex items-center gap-2">
            <TaskStateControl task={task} />
            <RescheduleTaskControl task={task} />
          </div>
        </li>
      ))}
    </ul>
  );
}
