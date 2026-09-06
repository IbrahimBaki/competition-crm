import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { configureAxe } from 'vitest-axe';
import { afterEach, describe, expect, it, vi } from 'vitest';
import i18n from '@/i18n';
import { LocaleProvider } from '@/i18n/LocaleProvider';
import { StaffShell } from '../StaffShell';

const mocks = vi.hoisted(() => ({
  logout: vi.fn(),
  markRead: vi.fn(),
  markAllRead: vi.fn(),
}));

vi.mock('@/auth/AuthProvider', () => ({
  useAuth: () => ({
    user: { id: 'u-1', name: 'Amina Youssef', email: 'amina@example.test', permission_keys: [], locale: 'en' },
    permissions: (globalThis as { __testPermissions?: string[] }).__testPermissions ?? [],
    logout: mocks.logout,
  }),
}));

vi.mock('@/features/workspace/notifications/useNotificationsQuery', () => ({
  useNotificationsQuery: () => ({
    query: { isLoading: false, isError: false, error: null, data: { items: [], meta: {} }, refetch: vi.fn() },
    unreadCount: 0,
  }),
}));

vi.mock('@/features/workspace/notifications/useNotificationMutations', () => ({
  useNotificationMutations: () => ({ markRead: mocks.markRead, markAllRead: mocks.markAllRead }),
}));

const axe = configureAxe({ rules: { 'color-contrast': { enabled: false }, region: { enabled: false } } });

function setPermissions(permissions: string[]) {
  (globalThis as { __testPermissions?: string[] }).__testPermissions = permissions;
}

// Rail's nav landmark. There is only a second one (the drawer's) while the
// mobile drawer is open, so [0] is always the rail's outside that scenario.
function railNav() {
  const [nav] = screen.getAllByRole('navigation', { name: 'Main navigation' });
  if (!nav) throw new Error('Expected the rail navigation landmark to be present');
  return nav;
}

function renderShell(path = '/') {
  return render(
    <LocaleProvider>
      <MemoryRouter initialEntries={[path]}>
        <Routes>
          <Route element={<StaffShell />}>
            <Route index element={<div>Workspace content</div>} />
            <Route path="workspace" element={<div>Workspace content</div>} />
            <Route path="tickets" element={<div>Tickets content</div>} />
            <Route path="tickets/:ticketId" element={<div>Ticket detail content</div>} />
            <Route path="admin/branches" element={<div>Branches content</div>} />
            <Route path="admin/branches/:branchId" element={<div>Branch detail content</div>} />
          </Route>
        </Routes>
      </MemoryRouter>
    </LocaleProvider>
  );
}

function renderArabic(path = '/') {
  // StaffTopBar reads locale through LocaleProvider, which resolves language
  // from localStorage on mount (and would otherwise stomp a bare
  // i18n.changeLanguage('ar') call back to 'en' during its own mount effect).
  localStorage.setItem('locale', 'ar');
  return renderShell(path);
}

afterEach(async () => {
  vi.clearAllMocks();
  setPermissions([]);
  localStorage.removeItem('locale');
  await i18n.changeLanguage('en');
});

describe('StaffShell navigation', () => {
  it('renders only the permission-free Workspace item when the user has no permissions', () => {
    setPermissions([]);
    renderShell('/');
    const nav = railNav();
    expect(within(nav).getByRole('link', { name: 'Workspace' })).toBeVisible();
    expect(within(nav).queryByRole('link', { name: 'Tickets' })).not.toBeInTheDocument();
    expect(within(nav).queryByText('Administration')).not.toBeInTheDocument();
  });

  it('shows a permission-gated item once its permission is granted, and hides it otherwise', () => {
    setPermissions(['customers.view']);
    renderShell('/');
    const nav = railNav();
    expect(within(nav).getByRole('link', { name: 'Customers' })).toBeVisible();
    expect(within(nav).queryByRole('link', { name: 'Tickets' })).not.toBeInTheDocument();
  });

  it('does not render the Administration group heading when every child is forbidden', () => {
    setPermissions([]);
    renderShell('/');
    expect(screen.queryByText('Administration')).not.toBeInTheDocument();
  });

  it('renders only the visible admin child, with the group auto-expanded on its own active route', () => {
    setPermissions(['org.branches.view.any']);
    renderShell('/admin/branches');
    const nav = railNav();
    expect(within(nav).getByText('Administration')).toBeVisible();
    expect(within(nav).getByRole('link', { name: 'Branches' })).toBeVisible();
    // "Departments" requires a different permission the mock user was not granted —
    // it must not exist in the tree at all (not merely hidden/disabled).
    expect(within(nav).queryByRole('link', { name: 'Departments', hidden: true })).not.toBeInTheDocument();
  });

  it('marks the exact route active and treats the "/workspace" alias as the Workspace item', () => {
    renderShell('/workspace');
    const nav = railNav();
    expect(within(nav).getByRole('link', { name: 'Workspace' })).toHaveAttribute('aria-current', 'page');
  });

  it('keeps the parent admin group expanded and marks the nested detail route active on its list item', () => {
    setPermissions(['org.branches.view.any']);
    renderShell('/admin/branches/9f1');
    const nav = railNav();
    const link = within(nav).getByRole('link', { name: 'Branches' });
    expect(link).toHaveAttribute('aria-current', 'page');
    // The disclosure must be open for the active child to be visible at all.
    expect(link).toBeVisible();
  });

  it('shows a two-level, landmarked breadcrumb for an admin child route with a working parent link', () => {
    setPermissions(['org.branches.view.any']);
    renderShell('/admin/branches');
    const breadcrumb = screen.getByRole('navigation', { name: 'Breadcrumb' });
    const parentLink = within(breadcrumb).getByRole('link', { name: 'Administration' });
    expect(parentLink).toHaveAttribute('href', '/admin');
    expect(within(breadcrumb).getByText('Branches')).toHaveAttribute('aria-current', 'page');
  });
});

describe('StaffShell composition', () => {
  it('renders a main landmark, a working skip link, and the routed content', () => {
    renderShell('/tickets');
    const main = screen.getByRole('main');
    expect(main).toHaveAttribute('id', 'main-content');
    expect(screen.getByText('Tickets content')).toBeVisible();
    const skipLink = screen.getByRole('link', { name: 'Skip to main content' });
    expect(skipLink).toHaveAttribute('href', '#main-content');
  });

  it('places the rail and topbar inside the V2 boundary but keeps routed (V1) content outside it', () => {
    const { container } = renderShell('/tickets');
    const content = screen.getByText('Tickets content');
    expect(content.closest('[data-ui="v2"]')).toBeNull();
    const nav = railNav();
    expect(nav.closest('[data-ui="v2"]')).not.toBeNull();
    // Sanity: the V2 boundary really is present in this render (not merely absent everywhere).
    expect(container.querySelector('[data-ui="v2"]')).not.toBeNull();
  });
});

describe('StaffShell account menu', () => {
  it('has an accessible trigger naming the signed-in user and calls the existing logout on selection', async () => {
    const user = userEvent.setup();
    renderShell('/');
    const trigger = screen.getByRole('button', { name: /Amina Youssef/ });
    await user.click(trigger);
    expect(screen.getByRole('menuitem', { name: /My account/ })).toBeVisible();
    const logoutItem = screen.getByRole('menuitem', { name: /Logout/ });
    await user.click(logoutItem);
    expect(mocks.logout).toHaveBeenCalledTimes(1);
  });
});

describe('StaffShell mobile navigation drawer', () => {
  it('opens an accessible, labelled drawer from the trigger and closes it on route selection', async () => {
    const user = userEvent.setup();
    renderShell('/');
    const trigger = screen.getByRole('button', { name: 'Open navigation menu' });
    await user.click(trigger);
    const dialog = screen.getByRole('dialog', { name: 'Navigation' });
    expect(dialog).toBeVisible();
    const drawerNav = within(dialog).getByRole('navigation', { name: 'Main navigation' });
    const drawerWorkspaceLink = within(drawerNav).getByRole('link', { name: 'Workspace' });
    await user.click(drawerWorkspaceLink);
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
    expect(trigger).toHaveFocus();
  });
});

describe('StaffShell RTL', () => {
  it('sets dir="rtl" on every V2 chrome boundary in Arabic', () => {
    const { container } = renderArabic('/');
    const boundaries = container.querySelectorAll('[data-ui="v2"]');
    expect(boundaries.length).toBeGreaterThan(0);
    boundaries.forEach((boundary) => expect(boundary).toHaveAttribute('dir', 'rtl'));
  });
});

describe('StaffShell accessibility', () => {
  it('has no axe violations in English', async () => {
    const { container } = renderShell('/');
    expect((await axe(container)).violations).toEqual([]);
  });

  it('has no axe violations in Arabic', async () => {
    const { container } = renderArabic('/');
    expect((await axe(container)).violations).toEqual([]);
  });
});

describe('StaffShell mobile drawer accessibility', () => {
  it('has no axe violations with the drawer open, and hides the desktop rail landmark from the accessibility tree', async () => {
    const user = userEvent.setup();
    const { container } = renderShell('/');
    await user.click(screen.getByRole('button', { name: 'Open navigation menu' }));
    await screen.findByRole('dialog', { name: 'Navigation' });
    // Radix's default modal behavior aria-hides the rest of the tree, so only
    // the drawer's own nav landmark should remain in the accessible tree.
    expect(screen.getAllByRole('navigation', { name: 'Main navigation' })).toHaveLength(1);
    expect((await axe(container)).violations).toEqual([]);
  });
});
