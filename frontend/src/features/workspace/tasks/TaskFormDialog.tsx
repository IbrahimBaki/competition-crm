import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useQueryClient } from '@tanstack/react-query';
import { useAuth } from '@/auth/AuthProvider';
import { getGetAgentTasksQueryKey } from '@/api/generated/workspace/workspace';
import { useCreateAgentTask, newIdempotencyKey, localDateTimeToWire } from '../api/wire';

interface TaskFormDialogProps {
  open: boolean;
  onClose: () => void;
  ticket?: { uuid: string; reference: string };
}

export function TaskFormDialog({ open, onClose, ticket }: TaskFormDialogProps) {
  const { t } = useTranslation();
  const { user } = useAuth();
  const queryClient = useQueryClient();

  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [dueAt, setDueAt] = useState('');
  const [dueInWorkingTime, setDueInWorkingTime] = useState(false);

  const mutation = useCreateAgentTask({
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: getGetAgentTasksQueryKey() });
      setTitle('');
      setDescription('');
      setDueAt('');
      setDueInWorkingTime(false);
      onClose();
    },
  });

  if (!open) return null;

  const canSubmit = title.trim().length > 0 && title.length <= 255 && !!user?.id;

  const submit = () => {
    if (!canSubmit || !user) return;
    mutation.mutate({
      ownerId: user.id,
      title: title.trim(),
      description: description.trim() || null,
      dueAt: dueAt ? localDateTimeToWire(dueAt) : null,
      dueInWorkingTime,
      ticketId: ticket?.uuid ?? null,
      idempotencyKey: newIdempotencyKey(),
    });
  };

  return (
    <div className="fixed inset-0 z-20 flex items-center justify-center bg-black/30 p-4">
      <div className="w-full max-w-md rounded bg-white p-4 shadow-lg">
        <h2 className="mb-3 text-sm font-semibold text-gray-800">{t('workspace.task_form.title')}</h2>

        {ticket && (
          <p className="mb-2 text-xs text-gray-500">
            {t('workspace.task_form.for_ticket', { reference: ticket.reference })}
          </p>
        )}

        <label className="mb-1 block text-xs font-medium text-gray-600">{t('workspace.task_form.title_label')}</label>
        <input
          value={title}
          onChange={(event) => setTitle(event.target.value)}
          maxLength={255}
          className="mb-3 w-full rounded border border-gray-300 p-2 text-sm"
        />

        <label className="mb-1 block text-xs font-medium text-gray-600">
          {t('workspace.task_form.description_label')}
        </label>
        <textarea
          value={description}
          onChange={(event) => setDescription(event.target.value)}
          rows={3}
          className="mb-3 w-full rounded border border-gray-300 p-2 text-sm"
        />

        <label className="mb-1 block text-xs font-medium text-gray-600">{t('workspace.task_form.due_at_label')}</label>
        <input
          type="datetime-local"
          value={dueAt}
          onChange={(event) => setDueAt(event.target.value)}
          className="mb-3 w-full rounded border border-gray-300 p-2 text-sm"
        />

        <label className="mb-3 flex items-center gap-2 text-xs text-gray-600">
          <input
            type="checkbox"
            checked={dueInWorkingTime}
            onChange={(event) => setDueInWorkingTime(event.target.checked)}
          />
          {t('workspace.task_form.due_in_working_time_label')}
        </label>

        {mutation.isError && <p className="mb-2 text-xs text-red-600">{t('workspace.task.error_generic')}</p>}

        <div className="flex justify-end gap-2">
          <button
            type="button"
            onClick={onClose}
            className="rounded px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100"
          >
            {t('workspace.task_form.cancel')}
          </button>
          <button
            type="button"
            onClick={submit}
            disabled={!canSubmit || mutation.isPending}
            className="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {t('workspace.task_form.submit')}
          </button>
        </div>
      </div>
    </div>
  );
}
