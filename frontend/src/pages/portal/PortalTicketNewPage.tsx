import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

export function PortalTicketNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  return (
    <div className="max-w-2xl mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-6">{t('portal.ticket_new.title')}</h1>
      <form onSubmit={(e) => { e.preventDefault(); navigate('/portal/tickets'); }} className="space-y-4">
        <input type="text" placeholder={t('portal.ticket_new.subject_label')} className="w-full px-3 py-2 border border-gray-300 rounded-md" required />
        <textarea placeholder={t('portal.ticket_new.description_label')} className="w-full px-3 py-2 border border-gray-300 rounded-md" required />
        <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-md">{t('portal.ticket_new.submit_button')}</button>
      </form>
    </div>
  );
}
