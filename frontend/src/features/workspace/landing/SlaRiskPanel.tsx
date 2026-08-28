import { useTranslation } from 'react-i18next';
import { EmptyState } from '@/shell/states/EmptyState';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { useSlaRiskTickets } from '../queues/useSlaRiskTickets';
import { WorkspacePanel } from './WorkspacePanel';

/**
 * Server-provided SLA fields never reach tickets/queues/mine today (see
 * .squad/gaps/35-482.md #18/#19) — this panel renders the plan's own
 * specified fallback (a translated "not available" EmptyState) rather than
 * deriving risk client-side. No Date arithmetic anywhere in this file.
 */
export function SlaRiskPanel() {
  const { t } = useTranslation();
  const { query, available, items } = useSlaRiskTickets(10);

  if (!available) {
    return (
      <WorkspacePanel title={t('workspace.panel.sla_risk.title')}>
        <EmptyState
          title={t('workspace.panel.sla_risk.unavailable_title')}
          description={t('workspace.panel.sla_risk.unavailable_description')}
        />
      </WorkspacePanel>
    );
  }

  return (
    <WorkspacePanel title={t('workspace.panel.sla_risk.title')} count={items.length}>
      <AsyncBoundary
        query={query}
        isEmpty={() => items.length === 0}
        empty={<EmptyState title={t('workspace.panel.sla_risk.empty_title')} />}
      >
        {() => (
          <ul className="divide-y divide-gray-100">
            {items.map((row) => (
              <li key={(row as { uuid: string }).uuid} className="py-2 text-sm">
                <span className="me-2 inline-block rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                  {t('workspace.panel.sla_risk.badge')}
                </span>
                {(row as { subject?: string }).subject}
              </li>
            ))}
          </ul>
        )}
      </AsyncBoundary>
    </WorkspacePanel>
  );
}
