import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { useNotificationsQuery } from './useNotificationsQuery';
import { useNotificationMutations } from './useNotificationMutations';
import { notificationTarget } from './notificationTarget';

interface NotificationMenuProps {
  onClose: () => void;
}

export function NotificationMenu({ onClose }: NotificationMenuProps) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { query } = useNotificationsQuery(10);
  const { markRead, markAllRead } = useNotificationMutations();

  return (
    <div className="absolute end-0 top-full z-10 mt-2 w-80 rounded border border-gray-200 bg-white shadow-lg">
      <div className="flex items-center justify-between border-b border-gray-100 p-3">
        <h2 className="text-sm font-semibold text-gray-800">{t('workspace.notifications.heading')}</h2>
        <button
          type="button"
          onClick={() => markAllRead()}
          className="text-xs font-medium text-blue-600 hover:text-blue-700"
        >
          {t('workspace.notifications.mark_all_read')}
        </button>
      </div>

      <AsyncBoundary
        query={query}
        isEmpty={(data) => data.items.length === 0}
        empty={<EmptyState title={t('workspace.notifications.empty')} />}
      >
        {(data) => (
          <ul className="max-h-96 overflow-y-auto">
            {data.items.map((notification) => (
              <li key={notification.uuid} className="border-b border-gray-50 last:border-0">
                <button
                  type="button"
                  onClick={() => {
                    if (!notification.readAt) markRead(notification.uuid);
                    navigate(notificationTarget(notification));
                    onClose();
                  }}
                  className={`block w-full p-3 text-start text-sm hover:bg-gray-50 ${
                    notification.readAt ? 'text-gray-500' : 'font-medium text-gray-900'
                  }`}
                >
                  {notification.subject || notification.body || notification.eventType}
                </button>
              </li>
            ))}
          </ul>
        )}
      </AsyncBoundary>
    </div>
  );
}
