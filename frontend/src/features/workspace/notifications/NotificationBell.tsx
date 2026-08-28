import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNotificationsQuery } from './useNotificationsQuery';
import { NotificationMenu } from './NotificationMenu';

export function NotificationBell() {
  const { t } = useTranslation();
  const [open, setOpen] = useState(false);
  const { unreadCount } = useNotificationsQuery(10);

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setOpen((current) => !current)}
        aria-label={t('workspace.notifications.bell_label')}
        className="relative rounded p-1.5 text-gray-600 hover:bg-gray-100 hover:text-gray-900"
      >
        <span aria-hidden="true">🔔</span>
        {unreadCount > 0 && (
          <span className="absolute -end-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white">
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        )}
      </button>
      {open && <NotificationMenu onClose={() => setOpen(false)} />}
    </div>
  );
}
