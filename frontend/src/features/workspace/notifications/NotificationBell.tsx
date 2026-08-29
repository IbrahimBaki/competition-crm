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
        className="relative ui-button ui-button--ghost"
      >
        <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" strokeWidth="1.8"><path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
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
