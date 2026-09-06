import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { configureAxe } from 'vitest-axe';
import { afterEach, describe, expect, it, vi } from 'vitest';
import i18n from '@/i18n';
import { WorkspacePage } from '@/pages/WorkspacePage';

const state = vi.hoisted(() => ({
  permissions: ['tickets.queue.view', 'workspace.tasks.view.own', 'workspace.tasks.create', 'notifications.view.own'],
  mine: { isLoading: false, isError: false, data: { items: [{ uuid: 'ticket-1', reference: 'SUP-101', subject: 'Cannot sign in', status: 'open', priority: 'high' }], meta: { total: 1 } }, refetch: vi.fn() },
  department: { isLoading: false, isError: false, data: { items: [{ uuid: 'ticket-2', reference: 'SUP-102', subject: 'Queue request', status: 'pending', priority: 'normal' }], meta: { total: 1 } }, refetch: vi.fn() },
  tasks: { isLoading: false, isError: false, data: { items: [{ uuid: 'task-1', title: 'Reply today', state: 'open', isOverdue: true, dueAt: '2026-09-02T10:00:00+00:00', owner: { uuid: 'agent-1', name: 'Amina' }, ticket: { uuid: 'ticket-1', reference: 'SUP-101' } }], meta: { total: 1 } }, refetch: vi.fn() },
  notifications: { isLoading: false, isError: false, data: { items: [{ uuid: 'notice-1', subject: 'Ticket assigned', body: 'SUP-101 moved to you', readAt: null, createdAt: '2026-09-02T10:00:00+00:00' }], meta: { total: 1 } }, refetch: vi.fn() },
  completeTask: vi.fn(),
}));

vi.mock('@/auth/AuthProvider', () => ({ useAuth: () => ({ user: { id: 'agent-1', department_ids: ['department-1'] }, permissions: state.permissions }) }));
vi.mock('@/auth/usePermissions', () => ({ usePermissions: () => ({ can: (key: string) => state.permissions.includes(key) }) }));
vi.mock('@/features/workspace/queues/useMyQueueQuery', () => ({ useMyQueueQuery: () => state.mine }));
vi.mock('@/features/workspace/queues/useDepartmentQueueQuery', () => ({ useDepartmentQueueQuery: () => state.department }));
vi.mock('@/features/workspace/queues/useSlaRiskTickets', () => ({ useSlaRiskTickets: () => ({ available: false, items: [] }) }));
vi.mock('@/features/workspace/tasks/useAgentTaskListQuery', () => ({ useAgentTaskListQuery: () => state.tasks }));
vi.mock('@/features/workspace/notifications/useNotificationsQuery', () => ({ useNotificationsQuery: () => ({ query: state.notifications, unreadCount: 1 }) }));
vi.mock('@/features/workspace/tasks/TaskFormDialog', () => ({ TaskFormDialog: () => null }));
vi.mock('@/features/workspace/tasks/useAgentTaskMutations', () => ({ useAgentTaskMutations: () => ({ changeState: state.completeTask, update: vi.fn(), changeStateState: { isPending: false, isError: false }, updateState: { isPending: false, isError: false } }) }));

const axe = configureAxe({ rules: { 'color-contrast': { enabled: false }, region: { enabled: false } } });
function renderRoute(path = '/') { return render(<MemoryRouter initialEntries={[path]}><Routes><Route path="/" element={<WorkspacePage />} /><Route path="/workspace" element={<WorkspacePage />} /><Route path="/tickets" element={<div>V1 tickets</div>} /></Routes></MemoryRouter>); }
function reset() { state.permissions = ['tickets.queue.view', 'workspace.tasks.view.own', 'workspace.tasks.create', 'notifications.view.own']; for (const query of [state.mine, state.department, state.tasks, state.notifications]) { query.isLoading = false; query.isError = false; } }
afterEach(async () => { reset(); state.completeTask.mockClear(); await i18n.changeLanguage('en'); });

describe('Workspace V2', () => {
  it('renders the populated operational ledgers and existing routes', async () => {
    const { container } = renderRoute();
    expect(screen.getByRole('heading', { name: 'Needs attention' })).toBeVisible();
    expect(screen.getByRole('heading', { name: 'My work' })).toBeVisible();
    expect(screen.getByText('Cannot sign in')).toBeVisible();
    expect(screen.getByText('Reply today')).toBeVisible();
    expect(screen.getByText('Ticket assigned')).toBeVisible();
    expect(screen.getByRole('link', { name: /Cannot sign in/ })).toHaveAttribute('href', '/tickets/ticket-1');
    expect(screen.getAllByRole('link', { name: 'View all' })[0]).toHaveAttribute('href', '/tickets?mode=mine');
    expect(container.querySelector('[data-ui="v2"]')).not.toBeNull();
    expect((await axe(container)).violations).toEqual([]);
  });

  it('keeps both aliases on the same Workspace V2 surface and leaves a V1 sibling outside it', () => {
    const workspace = renderRoute('/workspace');
    expect(workspace.container.querySelector('[data-ui="v2"]')).toContainElement(screen.getByRole('heading', { name: 'Workspace' }));
    workspace.unmount();
    const sibling = renderRoute('/tickets');
    expect(screen.getByText('V1 tickets').closest('[data-ui="v2"]')).toBeNull();
    sibling.unmount();
    renderRoute('/');
    expect(screen.getByRole('heading', { name: 'Workspace' })).toBeVisible();
  });

  it('renders independent loading, empty, error and permission-limited states', () => {
    state.mine.isLoading = true; state.tasks.isError = true; state.notifications.data = { items: [], meta: { total: 0 } };
    state.permissions = ['workspace.tasks.view.own'];
    renderRoute();
    expect(screen.getByText('Loading workspace information')).toBeVisible();
    expect(screen.getByText('This section could not be loaded')).toBeVisible();
    expect(screen.getByText('This information is not available to your account')).toBeVisible();
  });

  it('hides the queue and renders compact permission states when data is forbidden', () => {
    state.permissions = [];
    renderRoute();
    expect(screen.queryByRole('heading', { name: 'Department Queue' })).not.toBeInTheDocument();
    expect(screen.getAllByText('This information is not available to your account')).toHaveLength(2);
  });

  it('uses the existing task-state mutation from the V2 Complete action', async () => {
    const user = userEvent.setup();
    renderRoute();
    await user.click(screen.getByRole('button', { name: 'Complete' }));
    expect(state.completeTask).toHaveBeenCalledWith('task-1', 'done');
  });

  it('sets an Arabic Workspace boundary and preserves LTR ticket references', async () => {
    await i18n.changeLanguage('ar');
    const { container } = renderRoute();
    expect(container.querySelector('[data-ui="v2"]')).toHaveAttribute('dir', 'rtl');
    expect(container.querySelector('[data-ui="v2"]')).toHaveAttribute('lang', 'ar');
    expect(screen.getAllByText('SUP-101')[0]).toHaveClass('ds-bidi-value');
    expect((await axe(container)).violations).toEqual([]);
  });
});
