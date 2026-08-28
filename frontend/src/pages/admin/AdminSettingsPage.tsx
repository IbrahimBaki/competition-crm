import { useTranslation } from 'react-i18next';
import { AdminSettingsPanel } from '@/features/admin/security/AdminSettingsPanel';

export function AdminSettingsPage() {
  const { t } = useTranslation();

  return (
    <div>
      <h1 className="mb-6 text-3xl font-bold text-gray-900">{t('admin.security.settings.title')}</h1>
      <AdminSettingsPanel />
    </div>
  );
}
