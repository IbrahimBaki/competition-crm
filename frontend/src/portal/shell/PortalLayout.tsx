import { useTranslation } from 'react-i18next';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { usePortalAuth } from '../auth/PortalAuthProvider';

export function PortalLayout() {
  const { t } = useTranslation();
  const { user, logout, isAuthenticated } = usePortalAuth();
  const navigate = useNavigate();
  const { pathname } = useLocation();

  // Public pages that don't show auth nav
  const isAuthPage = pathname.includes('/login') || pathname.includes('/register') || pathname.includes('/verify') || pathname.includes('/track');

  return (
    <div className="min-h-screen bg-white flex flex-col">
      {/* Header */}
      <header className="bg-white border-b border-gray-200">
        <div className="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
          <h1 className="text-2xl font-bold text-gray-900">{t('portal.title')}</h1>
          {isAuthenticated && !isAuthPage && (
            <div className="flex items-center gap-4">
              <span className="text-sm text-gray-600">{user?.email}</span>
              <button
                onClick={logout}
                className="px-3 py-1 text-sm text-blue-600 hover:text-blue-900"
              >
                {t('portal.navigation.sign_out')}
              </button>
            </div>
          )}
        </div>
      </header>

      {/* Navigation */}
      {isAuthenticated && !isAuthPage && (
        <nav className="bg-gray-50 border-b border-gray-200">
          <div className="max-w-6xl mx-auto px-4 py-3 flex gap-6">
            <button
              onClick={() => navigate('/portal/tickets')}
              className="text-sm font-medium text-gray-700 hover:text-gray-900"
            >
              {t('portal.navigation.my_requests')}
            </button>
            <button
              onClick={() => navigate('/portal/help')}
              className="text-sm font-medium text-gray-700 hover:text-gray-900"
            >
              {t('portal.navigation.help')}
            </button>
          </div>
        </nav>
      )}

      {/* Content */}
      <main className="flex-1">
        <Outlet />
      </main>

      {/* Footer */}
      <footer className="bg-gray-50 border-t border-gray-200 mt-12">
        <div className="max-w-6xl mx-auto px-4 py-8 text-center text-sm text-gray-600">
          <p>© 2026 Support Portal</p>
        </div>
      </footer>
    </div>
  );
}
