import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Popover, PopoverContent, PopoverTrigger } from '@/design-system/composites/Popover';
import { Badge } from '@/design-system/primitives/Badge';
import { Spinner } from '@/design-system/primitives/Spinner';
import { icons } from '@/design-system/foundations/icons';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { useNotificationsQuery } from '@/features/workspace/notifications/useNotificationsQuery';
import { useNotificationMutations } from '@/features/workspace/notifications/useNotificationMutations';
import { notificationTarget } from '@/features/workspace/notifications/notificationTarget';
import styles from './NotificationsMenu.module.css';

/** Rebuilds the existing notifications feature's presentation in V2 chrome; the data/mutations are unchanged. */
export function NotificationsMenu() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);
  const { query, unreadCount } = useNotificationsQuery(10);
  const { markRead, markAllRead } = useNotificationMutations();

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <button type="button" className={styles.trigger} aria-label={t('workspace.notifications.bell_label')}>
          <icons.Bell aria-hidden="true" />
          {unreadCount > 0 && (
            <Badge tone="danger" className={styles.badge}>{unreadCount > 99 ? '99+' : unreadCount}</Badge>
          )}
        </button>
      </PopoverTrigger>
      <PopoverContent align="end" className={styles.content}>
        <div className={styles.header}>
          <h2 className={styles.heading}>{t('workspace.notifications.heading')}</h2>
          <button type="button" className={styles.markAll} onClick={() => markAllRead()}>
            {t('workspace.notifications.mark_all_read')}
          </button>
        </div>
        <AsyncBoundary
          query={query}
          isEmpty={(data) => data.items.length === 0}
          loading={<p className={styles.state}><Spinner /> {t('shell.notifications.loading')}</p>}
          empty={<p className={styles.state}>{t('workspace.notifications.empty')}</p>}
        >
          {(data) => (
            <ul className={styles.list}>
              {data.items.map((notification) => (
                <li key={notification.uuid}>
                  <button
                    type="button"
                    className={[styles.item, notification.readAt ? '' : styles.unread].filter(Boolean).join(' ')}
                    onClick={() => {
                      if (!notification.readAt) markRead(notification.uuid);
                      setOpen(false);
                      navigate(notificationTarget(notification));
                    }}
                  >
                    {notification.subject || notification.body || notification.eventType}
                  </button>
                </li>
              ))}
            </ul>
          )}
        </AsyncBoundary>
      </PopoverContent>
    </Popover>
  );
}
