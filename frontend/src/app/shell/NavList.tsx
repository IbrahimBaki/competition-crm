import { useEffect, useState, type ReactNode } from 'react';
// Deliberately Link, not NavLink: NavLink treats `aria-current`/`className`
// as "value to apply when *its own* internal isActive is true" rather than
// a plain pass-through, which silently drops our route-hierarchy-aware
// active state (e.g. the "/workspace" alias, or a parent staying active on
// a nested detail route). We already compute `active` correctly ourselves.
import { Link, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import type { NavItem } from '@/shell/navigation';
import { icons } from '@/design-system/foundations/icons';
import { hasActiveDescendant, isNavItemActive } from './navigation.helpers';
import styles from './NavList.module.css';

const NAV_ICONS: Record<string, ReactNode> = {
  workspace: <icons.Home aria-hidden="true" className={styles.icon} />,
  tickets: <icons.Inbox aria-hidden="true" className={styles.icon} />,
  customers: <icons.Users aria-hidden="true" className={styles.icon} />,
  knowledge: <icons.BookOpen aria-hidden="true" className={styles.icon} />,
  reports: <icons.BarChart3 aria-hidden="true" className={styles.icon} />,
  dashboard: <icons.LayoutDashboard aria-hidden="true" className={styles.icon} />,
  admin: <icons.ShieldCheck aria-hidden="true" className={styles.icon} />,
};

interface NavListProps {
  items: readonly NavItem[];
  onNavigate?: () => void;
}

/** Renders the staff navigation tree. Shared by the desktop rail and the mobile drawer. */
export function NavList({ items, onNavigate }: NavListProps) {
  const { t } = useTranslation();
  const { pathname } = useLocation();

  return (
    <nav aria-label={t('shell.nav.landmark_label')} className={styles.nav}>
      <ul className={styles.list}>
        {items.map((item) => (
          <li key={item.id}>
            {item.children ? (
              <NavGroup item={item} pathname={pathname} onNavigate={onNavigate} />
            ) : (
              <NavItemLink item={item} pathname={pathname} onNavigate={onNavigate} />
            )}
          </li>
        ))}
      </ul>
    </nav>
  );
}

function NavItemLink({ item, pathname, onNavigate }: { item: NavItem; pathname: string; onNavigate?: () => void }) {
  const { t } = useTranslation();
  const active = isNavItemActive(pathname, item);
  return (
    <Link
      to={item.path}
      onClick={onNavigate}
      aria-current={active ? 'page' : undefined}
      className={[styles.link, active ? styles.active : ''].filter(Boolean).join(' ')}
    >
      {NAV_ICONS[item.icon ?? '']}
      <span className={styles.label}>{t(item.labelKey)}</span>
    </Link>
  );
}

function NavGroup({ item, pathname, onNavigate }: { item: NavItem; pathname: string; onNavigate?: () => void }) {
  const { t } = useTranslation();
  const children = item.children ?? [];
  const activeDescendant = hasActiveDescendant(pathname, children);
  const [open, setOpen] = useState(activeDescendant);

  // Auto-expand when navigation (address bar, back/forward) lands on a child
  // route; never auto-collapse a group the user chose to leave open.
  useEffect(() => {
    if (activeDescendant) setOpen(true);
  }, [activeDescendant]);

  return (
    <details
      className={styles.group}
      open={open}
      onToggle={(event) => setOpen((event.target as HTMLDetailsElement).open)}
    >
      <summary className={[styles.groupSummary, activeDescendant ? styles.groupSummaryActive : ''].filter(Boolean).join(' ')}>
        {NAV_ICONS[item.icon ?? '']}
        <span className={styles.label}>{t(item.labelKey)}</span>
        <icons.ChevronDown aria-hidden="true" className={styles.chevron} />
      </summary>
      <ul className={styles.groupList} role="group">
        {children.map((child) => {
          const active = isNavItemActive(pathname, child);
          return (
            <li key={child.id}>
              <Link
                to={child.path}
                onClick={onNavigate}
                aria-current={active ? 'page' : undefined}
                className={[styles.childLink, active ? styles.active : ''].filter(Boolean).join(' ')}
              >
                {t(child.labelKey)}
              </Link>
            </li>
          );
        })}
      </ul>
    </details>
  );
}
