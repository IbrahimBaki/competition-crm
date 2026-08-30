import { useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { normaliseApiError } from '@/api/http/errors';
import { useInviteUser } from '../api/wire';

interface InviteUserDialogProps {
  onClose: () => void;
  onInvited: () => void;
}

// `InviteUserRequest::rules()` validates only `email` — no roles or
// branch/department assignment at invite time (the plan's generated
// `postUsersInviteBody` claims a `roles` field; that is spec drift, the
// real FormRequest has none). Role/placement assignment happens after
// acceptance via the role- and placement-attach actions.
export function InviteUserDialog({ onClose, onInvited }: InviteUserDialogProps) {
  const { t } = useTranslation();
  const [email, setEmail] = useState('');
  const [error, setError] = useState<string | undefined>();
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  const mutation = useInviteUser({
    onSuccess: onInvited,
    onError: (err) => {
      const normalised = normaliseApiError(err);
      setError(normalised.message);
      setFieldErrors(normalised.fieldErrors);
    },
  });

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    setError(undefined);
    setFieldErrors({});
    mutation.mutate({ email: email.trim() });
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div role="dialog" aria-modal="true" className="w-full max-w-sm rounded bg-white p-5 shadow-lg">
        <h2 className="mb-3 text-lg font-semibold text-gray-900">{t('admin.security.user.invite_dialog_title')}</h2>
        <form onSubmit={handleSubmit} className="space-y-3">
          <div>
            <label htmlFor="invite-email" className="mb-1 block text-xs font-medium text-gray-600">
              {t('admin.security.user.email_label')}
            </label>
            <input
              id="invite-email"
              type="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              required
              className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
            />
            {fieldErrors.email?.map((msg) => (
              <p key={msg} className="mt-1 text-xs text-red-600">
                {msg}
              </p>
            ))}
          </div>

          {error && <p className="text-sm text-red-600">{error}</p>}

          <div className="flex justify-end gap-2 pt-2">
            <button
              type="button"
              onClick={onClose}
              disabled={mutation.isPending}
              className="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              {t('admin.organisation.actions.cancel')}
            </button>
            <button
              type="submit"
              disabled={email.trim().length === 0 || mutation.isPending}
              className="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {t('admin.security.user.invite_submit')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
