import { useTranslation } from 'react-i18next';
import { NavLink, Outlet, useNavigate, useLocation } from 'react-router-dom';
import { Button } from '@/components/ui';
import { usePortalAuth } from '../auth/PortalAuthProvider';

export function PortalLayout() {
  const { t } = useTranslation();
  const { user, logout, isAuthenticated } = usePortalAuth();
  const navigate = useNavigate();
  const { pathname } = useLocation();

  // Public pages that don't show auth nav
  const isAuthPage = pathname.includes('/login') || pathname.includes('/register') || pathname.includes('/verify') || pathname.includes('/track');

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      <a className="skip-link" href="#portal-main">Skip to main content</a>
      {/* Header */}
      <header className="bg-white border-b border-gray-200">
        <div className="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
          <button className="flex items-center gap-3 text-left" onClick={() => navigate('/portal/help')}><span className="brand-mark" aria-hidden="true">S</span><span className="text-xl font-bold text-gray-900">{t('portal.title')}</span></button>
          {isAuthenticated && !isAuthPage && (
            <div className="flex items-center gap-4">
              <span className="text-sm text-gray-600">{user?.email}</span>
              <Button variant="secondary" onClick={() => void logout()}>
                {t('portal.navigation.sign_out')}
              </Button>
            </div>
          )}
        </div>
      </header>

      {/* Navigation */}
      {isAuthenticated && !isAuthPage && (
        <nav className="bg-gray-50 border-b border-gray-200">
          <div className="max-w-6xl mx-auto px-4 py-3 flex gap-6">
            <NavLink to="/portal/tickets" className={({ isActive }) => `text-sm font-semibold ${isActive ? 'text-blue-700' : 'text-slate-600'}`}>
              {t('portal.navigation.my_requests')}
            </NavLink>
            <NavLink to="/portal/help" className={({ isActive }) => `text-sm font-semibold ${isActive ? 'text-blue-700' : 'text-slate-600'}`}>
              {t('portal.navigation.help')}
            </NavLink>
            <NavLink to="/portal/account" className={({ isActive }) => `text-sm font-semibold ${isActive ? 'text-blue-700' : 'text-slate-600'}`}>Account</NavLink>
          </div>
        </nav>
      )}

      {/* Content */}
      <main id="portal-main" className="flex-1" tabIndex={-1}>
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
