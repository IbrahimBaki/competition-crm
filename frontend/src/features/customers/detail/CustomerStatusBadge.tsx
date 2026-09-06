// Shared status-badge presentation, used by the full customer list/detail
// screens (Story 481) and the ticket-detail-embedded mini customer panel
// (frontend/src/features/tickets/detail/CustomerContextPanel.tsx, Story
// 33/34). Each caller supplies its own already-translated `label` so the
// two screens can keep their own i18n namespaces (`customers.status.*` vs
// `tickets.customer_context.status.*`) while sharing the status -> color
// mapping instead of duplicating it.
import styles from './CustomerRecordV2.module.css';
const STATUS_BADGE_CLASS: Record<string, string> = {
  active: 'bg-green-100 text-green-800',
  blocked: 'bg-red-100 text-red-800',
  anonymised: 'bg-gray-200 text-gray-700',
};

interface CustomerStatusBadgeProps {
  status: string;
  label: string;
}

export function CustomerStatusBadge({ status, label }: CustomerStatusBadgeProps) {
  return (
    <span
      className={`${styles.badge} ${status === 'active' ? styles.statusActive : status === 'blocked' ? styles.statusBlocked : ''} ${STATUS_BADGE_CLASS[status] ?? 'bg-gray-100 text-gray-700'}`}
    >
      {label}
    </span>
  );
}
