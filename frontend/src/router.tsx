import { createBrowserRouter, Navigate } from 'react-router-dom';
import { LoginPage } from './pages/LoginPage';
import { TwoFactorPage } from './pages/TwoFactorPage';
import { WorkspacePage } from './pages/WorkspacePage';
import { TicketsPage } from './pages/TicketsPage';
import { TicketDetailPage } from './pages/TicketDetailPage';
import { CustomersPage } from './pages/CustomersPage';
import { CustomerDetailPage } from './pages/CustomerDetailPage';
import { AdminIndexPage } from './pages/admin/AdminIndexPage';
import { BranchesPage } from './pages/admin/BranchesPage';
import { BranchDetailPage } from './pages/admin/BranchDetailPage';
import { DepartmentsPage } from './pages/admin/DepartmentsPage';
import { DepartmentDetailPage } from './pages/admin/DepartmentDetailPage';
import { TeamsPage } from './pages/admin/TeamsPage';
import { TeamDetailPage } from './pages/admin/TeamDetailPage';
import { UsersPage } from './pages/admin/UsersPage';
import { RolesPage } from './pages/admin/RolesPage';
import { RoleDetailPage } from './pages/admin/RoleDetailPage';
import { TicketCataloguePage } from './pages/admin/TicketCataloguePage';
import { SlaPoliciesPage } from './pages/admin/SlaPoliciesPage';
import { AutomationRulesPage } from './pages/admin/AutomationRulesPage';
import { ChannelsPage } from './pages/admin/ChannelsPage';
import { AdminSettingsPage } from './pages/admin/AdminSettingsPage';
import { NotFoundState } from './shell/states/NotFoundState';
import { AppLayout } from './shell/AppLayout';
import { ProtectedRoute } from './auth/ProtectedRoute';
import { RouteErrorBoundary } from './shell/RouteErrorBoundary';
import { PERMISSIONS } from './auth/permissions';

export const router = createBrowserRouter([
  {
    path: '/login',
    element: <LoginPage />,
  },
  {
    path: '/login/two-factor',
    element: <TwoFactorPage />,
  },
  {
    path: '/',
    element: (
      <ProtectedRoute>
        <AppLayout />
      </ProtectedRoute>
    ),
    errorElement: <RouteErrorBoundary />,
    children: [
      {
        index: true,
        element: <WorkspacePage />,
      },
      {
        path: 'dashboard',
        element: <Navigate to="/" replace />,
      },
      {
        path: 'workspace',
        element: <WorkspacePage />,
      },
      {
        path: 'tickets',
        element: (
          <ProtectedRoute permission={PERMISSIONS.TICKETS_VIEW_ANY}>
            <TicketsPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'tickets/:ticketId',
        element: (
          <ProtectedRoute permission={PERMISSIONS.TICKETS_VIEW_ANY}>
            <TicketDetailPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'customers',
        element: (
          <ProtectedRoute permission={PERMISSIONS.CUSTOMERS_VIEW}>
            <CustomersPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'customers/:customerId',
        element: (
          <ProtectedRoute permission={PERMISSIONS.CUSTOMERS_VIEW}>
            <CustomerDetailPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin',
        element: <AdminIndexPage />,
      },
      {
        path: 'admin/branches',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ORG_BRANCHES_VIEW_ANY}>
            <BranchesPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/branches/:branchId',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ORG_BRANCHES_VIEW_ANY}>
            <BranchDetailPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/departments',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ORG_DEPARTMENTS_VIEW_ANY}>
            <DepartmentsPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/departments/:departmentId',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ORG_DEPARTMENTS_VIEW_ANY}>
            <DepartmentDetailPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/teams',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ORG_TEAMS_VIEW_ANY}>
            <TeamsPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/teams/:teamId',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ORG_TEAMS_VIEW_ANY}>
            <TeamDetailPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/users',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ADMIN_USERS_MANAGE}>
            <UsersPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/roles',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ADMIN_ROLES_MANAGE}>
            <RolesPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/roles/:roleId',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ADMIN_ROLES_MANAGE}>
            <RoleDetailPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/ticket-catalogue',
        element: (
          <ProtectedRoute permission={PERMISSIONS.TICKETS_STATUSES_MANAGE}>
            <TicketCataloguePage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/sla-policies',
        element: (
          <ProtectedRoute permission={PERMISSIONS.SLA_POLICIES_VIEW}>
            <SlaPoliciesPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/automation-rules',
        element: (
          <ProtectedRoute permission={PERMISSIONS.AUTOMATION_RULES_VIEW}>
            <AutomationRulesPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/channels',
        element: (
          <ProtectedRoute permission={PERMISSIONS.CHANNELS_EMAIL_REPLAY_LIST}>
            <ChannelsPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/settings',
        element: (
          <ProtectedRoute permission={PERMISSIONS.ADMIN_USERS_MANAGE_TWO_FACTOR_POLICY}>
            <AdminSettingsPage />
          </ProtectedRoute>
        ),
      },
    ],
  },
  {
    path: '*',
    element: <NotFoundState />,
  },
]);
