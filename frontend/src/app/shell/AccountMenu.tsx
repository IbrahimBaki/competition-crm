import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '@/auth/AuthProvider';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/design-system/composites/DropdownMenu';
import { icons } from '@/design-system/foundations/icons';
import styles from './AccountMenu.module.css';

function initialsOf(name: string | undefined): string {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/).slice(0, 2);
  const initials = parts.map((part) => part[0]?.toUpperCase() ?? '').join('');
  return initials || '?';
}

/** Account trigger + menu. Reuses AuthProvider's existing user/logout — no auth logic here. */
export function AccountMenu() {
  const { t } = useTranslation();
  const { user, logout } = useAuth();

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button type="button" className={styles.trigger} aria-label={t('shell.account.trigger_label', { name: user?.name ?? '' })}>
          <span className={styles.avatar} aria-hidden="true">{initialsOf(user?.name)}</span>
          <span className={styles.name}>{user?.name}</span>
        </button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <div className={styles.header}>
          <p className={styles.headerName}>{user?.name}</p>
          <p className={styles.headerEmail}><span className="ds-bidi-value">{user?.email}</span></p>
        </div>
        <DropdownMenuItem asChild>
          <Link to="/account" className={styles.itemLink}>
            <icons.UserRound aria-hidden="true" className={styles.itemIcon} />
            {t('shell.account.link')}
          </Link>
        </DropdownMenuItem>
        <DropdownMenuItem className={[styles.itemLink, styles.itemDanger].join(' ')} onSelect={() => { void logout(); }}>
          <icons.LogOut aria-hidden="true" className={styles.itemIcon} />
          {t('nav.logout')}
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
