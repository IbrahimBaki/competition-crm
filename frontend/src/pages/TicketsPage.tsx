import { useTranslation } from 'react-i18next';
import { EmptyState } from '@/shell/states/EmptyState';

export function TicketsPage() {
  const { t } = useTranslation();

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-8">{t('pages.tickets.title')}</h1>
      <EmptyState
        title={t('pages.tickets.title')}
        description="Tickets feature coming soon..."
      />
    </div>
  );
}
