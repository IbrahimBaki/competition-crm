import { useAuth } from '@/auth/AuthProvider';
import { useTranslation } from 'react-i18next';
import { EmptyState } from '@/shell/states/EmptyState';

export function DashboardPage() {
  const { user, permissions } = useAuth();
  const { t } = useTranslation();

  if (!permissions || permissions.length === 0) {
    return (
      <div>
        <h1 className="text-3xl font-bold text-gray-900 mb-8">{t('pages.dashboard.title')}</h1>
        <EmptyState
          title={t('pages.dashboard.title')}
          description={t('pages.dashboard.no_modules')}
        />
      </div>
    );
  }

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-2">{t('pages.dashboard.title')}</h1>
      <p className="text-gray-600 mb-8">
        {t('pages.dashboard.welcome')}, <span className="font-semibold">{user?.name}</span>!
      </p>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white p-6 rounded-lg shadow border border-gray-200">
          <h3 className="text-lg font-semibold text-gray-900 mb-2">Quick Stats</h3>
          <p className="text-gray-600">Dashboard content coming soon...</p>
        </div>
      </div>
    </div>
  );
}
