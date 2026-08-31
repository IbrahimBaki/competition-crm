import { useState } from 'react';
import { useAuth } from '@/auth/AuthProvider';
import { useTranslation } from 'react-i18next';
import { useLocale } from '@/i18n/LocaleProvider';
import { NotificationBell } from '@/features/workspace/notifications/NotificationBell';

export function TopBar({ menuOpen, onMenu }: { menuOpen: boolean; onMenu: () => void }) {
  const { user, logout } = useAuth();
  const { t, i18n } = useTranslation();
  const { setLocale } = useLocale();
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const isRTL = document.documentElement.dir === 'rtl';

  const toggleLocale = async () => {
    const newLocale = i18n.language === 'en' ? 'ar' : 'en';
    await i18n.changeLanguage(newLocale);
    setLocale(newLocale);
  };

  return (
    <header className="app-topbar">
      <div className="flex items-center gap-3">
        <button type="button" className="mobile-menu-button ui-button ui-button--ghost" onClick={onMenu} aria-label={menuOpen ? 'Close navigation' : 'Open navigation'} aria-controls="primary-navigation" aria-expanded={menuOpen}><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
        <div className="topbar-context"><span className="text-xs font-semibold uppercase tracking-wider text-slate-500">Workspace</span><div className="font-semibold text-slate-900">{import.meta.env.VITE_APP_NAME}</div></div>
      </div>

      <div className="flex items-center gap-4">
        {/* Locale switcher */}
        <button
          onClick={toggleLocale}
          className="ui-button ui-button--secondary text-sm"
        >
          {i18n.language === 'en' ? 'العربية' : 'English'}
        </button>

        <NotificationBell />

        {/* User menu */}
        <div className="relative">
          <button
            onClick={() => setUserMenuOpen(!userMenuOpen)}
            className="ui-button ui-button--ghost"
            aria-expanded={userMenuOpen}
          >
            <div className="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-semibold">
              {user?.name?.charAt(0).toUpperCase()}
            </div>
            <span className="topbar-user-name text-sm font-medium">{user?.name}</span>
          </button>

          {userMenuOpen && (
            <div className={`absolute top-full ${isRTL ? 'left-0' : 'right-0'} mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg z-10`} role="menu">
              <div className="p-3 border-b border-gray-100">
                <p className="text-sm font-medium text-gray-900">{user?.name}</p>
                <p className="text-xs text-gray-500">{user?.email}</p>
              </div>
              <button
                onClick={() => {
                  logout();
                  setUserMenuOpen(false);
                }}
                className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                {t('nav.logout')}
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
