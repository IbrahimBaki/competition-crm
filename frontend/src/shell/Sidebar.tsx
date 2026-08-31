import { NavLink } from 'react-router-dom';
import { useVisibleNavigation } from './useVisibleNavigation';
import { useTranslation } from 'react-i18next';

function NavIcon() {
  return <svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" strokeWidth="1.8"><rect x="4" y="4" width="16" height="16" rx="4"/><path d="M8 12h8M12 8v8"/></svg>;
}

export function Sidebar({ open = false, onNavigate }: { open?: boolean; onNavigate?: () => void }) {
  const navigation = useVisibleNavigation();
  const { t } = useTranslation();

  return (
    <aside id="primary-navigation" className={`app-sidebar ${open ? 'is-open' : ''}`} aria-label="Main navigation">
      <div className="app-sidebar__brand">
        <span className="brand-mark" aria-hidden="true">S</span>
        <div><strong>Support CRM</strong><div className="text-xs text-slate-400">Operations workspace</div></div>
      </div>
      <nav>
        {navigation.map((item) => (
          <div key={item.id}>
            {item.children ? (
              <details className="nav-section group">
                <summary className="nav-summary">
                  <NavIcon />
                  <span className="flex-1">{t(item.labelKey)}</span>
                  <span aria-hidden="true" className="text-xs group-open:rotate-180 transition-transform">⌄</span>
                </summary>
                <div className="nav-children">
                  {item.children.map((child) => (
                    <NavLink
                      key={child.id}
                      to={child.path}
                      onClick={onNavigate}
                      className={({ isActive }) => `nav-link text-sm ${isActive ? 'active' : ''}`}
                    >
                      {t(child.labelKey)}
                    </NavLink>
                  ))}
                </div>
              </details>
            ) : (
              <NavLink
                to={item.path}
                onClick={onNavigate}
                className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
              >
                <NavIcon />
                {t(item.labelKey)}
              </NavLink>
            )}
          </div>
        ))}
      </nav>
    </aside>
  );
}
