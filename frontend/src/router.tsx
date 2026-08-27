import { createBrowserRouter } from 'react-router-dom';
import { LoginPage } from './pages/LoginPage';
import { TwoFactorPage } from './pages/TwoFactorPage';
import { DashboardPage } from './pages/DashboardPage';
import { TicketsPage } from './pages/TicketsPage';
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
        element: <DashboardPage />,
      },
      {
        path: 'tickets',
        element: (
          <ProtectedRoute permission={PERMISSIONS.TICKETS_VIEW_ANY}>
            <TicketsPage />
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
