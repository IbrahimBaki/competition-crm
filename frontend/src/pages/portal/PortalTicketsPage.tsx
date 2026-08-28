import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

export function PortalTicketsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  return (
    <div className="max-w-4xl mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-3xl font-bold">{t('portal.tickets.title')}</h1>
        <button onClick={() => navigate('/portal/tickets/new')} className="px-4 py-2 bg-blue-600 text-white rounded-md">
          {t('portal.tickets.new_button')}
        </button>
      </div>
      <div className="text-center text-gray-600">{t('portal.tickets.empty')}</div>
    </div>
  );
}
