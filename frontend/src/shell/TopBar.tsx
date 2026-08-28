import { useState } from 'react';
import { useAuth } from '@/auth/AuthProvider';
import { useTranslation } from 'react-i18next';
import { useLocale } from '@/i18n/LocaleProvider';
import { NotificationBell } from '@/features/workspace/notifications/NotificationBell';

export function TopBar() {
  const { user, logout } = useAuth();
  const { t, i18n } = useTranslation();
  const { setLocale } = useLocale();
  const [menuOpen, setMenuOpen] = useState(false);
  const isRTL = document.documentElement.dir === 'rtl';

  const toggleLocale = async () => {
    const newLocale = i18n.language === 'en' ? 'ar' : 'en';
    await i18n.changeLanguage(newLocale);
    setLocale(newLocale);
  };

  return (
    <header className="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <div className="text-gray-800 font-medium">{import.meta.env.VITE_APP_NAME}</div>

      <div className="flex items-center gap-4">
        {/* Locale switcher */}
        <button
          onClick={toggleLocale}
          className="text-gray-600 hover:text-gray-900 text-sm font-medium px-3 py-1 rounded border border-gray-300 hover:border-gray-400 transition"
        >
          {i18n.language === 'en' ? 'العربية' : 'English'}
        </button>

        <NotificationBell />

        {/* User menu */}
        <div className="relative">
          <button
            onClick={() => setMenuOpen(!menuOpen)}
            className="flex items-center gap-2 text-gray-700 hover:text-gray-900"
          >
            <div className="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-semibold">
              {user?.name?.charAt(0).toUpperCase()}
            </div>
            <span className="text-sm font-medium">{user?.name}</span>
          </button>

          {menuOpen && (
            <div className={`absolute top-full ${isRTL ? 'left-0' : 'right-0'} mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg z-10`}>
              <div className="p-3 border-b border-gray-100">
                <p className="text-sm font-medium text-gray-900">{user?.name}</p>
                <p className="text-xs text-gray-500">{user?.email}</p>
              </div>
              <button
                onClick={() => {
                  logout();
                  setMenuOpen(false);
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
