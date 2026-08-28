import { useTranslation } from 'react-i18next';
import { MyTicketsPanel } from '@/features/workspace/landing/MyTicketsPanel';
import { DepartmentQueuePanel } from '@/features/workspace/landing/DepartmentQueuePanel';
import { SlaRiskPanel } from '@/features/workspace/landing/SlaRiskPanel';
import { OverdueTasksPanel } from '@/features/workspace/landing/OverdueTasksPanel';

export function WorkspacePage() {
  const { t } = useTranslation();

  return (
    <div>
      <h1 className="mb-6 text-3xl font-bold text-gray-900">{t('workspace.title')}</h1>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        <MyTicketsPanel />
        <DepartmentQueuePanel />
        <SlaRiskPanel />
        <OverdueTasksPanel />
      </div>
    </div>
  );
}
