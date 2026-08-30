import type { TicketLifecycleType, TicketPriority } from '../types';

export function statusLabelKey(status: TicketLifecycleType): string {
  return `tickets.status.${status}`;
}

export function priorityLabelKey(priority: TicketPriority): string {
  return `tickets.priority.${priority}`;
}

const STATUS_BADGE_CLASSES: Record<TicketLifecycleType, string> = {
  new: 'bg-blue-100 text-blue-800',
  open: 'bg-green-100 text-green-800',
  pending: 'bg-amber-100 text-amber-800',
  resolved: 'bg-teal-100 text-teal-800',
  closed: 'bg-gray-200 text-gray-700',
  spam: 'bg-red-100 text-red-800',
};

export function statusBadgeClass(status: TicketLifecycleType): string {
  return STATUS_BADGE_CLASSES[status] ?? 'bg-gray-100 text-gray-700';
}

const PRIORITY_BADGE_CLASSES: Record<TicketPriority, string> = {
  low: 'bg-gray-100 text-gray-700',
  normal: 'bg-blue-100 text-blue-800',
  high: 'bg-orange-100 text-orange-800',
  urgent: 'bg-red-100 text-red-800',
};

export function priorityBadgeClass(priority: TicketPriority): string {
  return PRIORITY_BADGE_CLASSES[priority] ?? 'bg-gray-100 text-gray-700';
}
