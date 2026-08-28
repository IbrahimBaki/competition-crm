import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient } from '@tanstack/react-query';
import { getGetAgentTasksQueryKey } from '@/api/generated/workspace/workspace';
import { useUpdateAgentTask, newIdempotencyKey, localDateTimeToWire } from '../api/wire';
import type { AgentTask } from '../types';

interface RescheduleTaskControlProps {
  task: AgentTask;
}

export function RescheduleTaskControl({ task }: RescheduleTaskControlProps) {
  const { t } = useTranslation();
  const queryClient = useQueryClient();
  const [open, setOpen] = useState(false);
  const [value, setValue] = useState('');

  const mutation = useUpdateAgentTask({
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: getGetAgentTasksQueryKey() });
      setOpen(false);
    },
  });

  if (task.state === 'done' || task.state === 'cancelled') {
    return null;
  }

  if (!open) {
    return (
      <button
        type="button"
        onClick={() => setOpen(true)}
        className="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200"
      >
        {t('workspace.task.action_reschedule')}
      </button>
    );
  }

  const submit = () => {
    const dueAt = localDateTimeToWire(value);
    if (!dueAt) return;
    mutation.mutate({ task: task.uuid, input: { dueAt }, idempotencyKey: newIdempotencyKey() });
  };

  return (
    <div className="flex items-center gap-1">
      <input
        type="datetime-local"
        value={value}
        onChange={(event) => setValue(event.target.value)}
        className="rounded border border-gray-300 px-1 py-0.5 text-xs"
      />
      <button
        type="button"
        onClick={submit}
        disabled={!value || mutation.isPending}
        className="rounded bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-50"
      >
        {t('workspace.task.action_save')}
      </button>
      <button
        type="button"
        onClick={() => setOpen(false)}
        className="rounded px-2 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100"
      >
        {t('workspace.task.action_cancel_edit')}
      </button>
    </div>
  );
}
