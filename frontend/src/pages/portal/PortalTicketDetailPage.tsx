import { useTranslation } from 'react-i18next';

export function PortalTicketDetailPage() {
  const { t } = useTranslation();
  return <div className="max-w-4xl mx-auto px-4 py-8"><h1>{t('portal.ticket_detail.title')}</h1></div>;
}
