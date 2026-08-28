import type { ReactElement, ReactNode } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { MemoryRouter } from 'react-router-dom';
import { I18nextProvider } from 'react-i18next';
import i18next from 'i18next';
import { initReactI18next } from 'react-i18next';
import { render } from '@testing-library/react';
import { AuthContext } from '@/auth/AuthProvider';
import type { User } from '@/auth/session';
import en from '@/i18n/en.json';

// Load the real translation file rather than a hand-picked subset, so a test
// doesn't pass or fail depending on whether someone remembered to mirror a
// key here.
const testI18n = i18next.createInstance();
testI18n.use(initReactI18next).init({
  lng: 'en',
  fallbackLng: 'en',
  resources: { en: { translation: en } },
  interpolation: { escapeValue: false },
});

interface RenderOptions {
  permissions?: string[];
}

function buildAuthValue(permissions: string[]) {
  const user: User = {
    id: 'agent-uuid',
    email: 'agent@example.test',
    name: 'Test Agent',
    locale: 'en',
    available_locales: ['en', 'ar'],
    permission_keys: permissions,
    primary_branch_id: null,
    department_ids: [],
  };

  return {
    status: 'authenticated' as const,
    user,
    permissions,
    login: async () => {},
    completeTwoFactor: async () => {},
    logout: async () => {},
    reload: async () => {},
  };
}

export function renderWithProviders(ui: ReactElement, { permissions = [] }: RenderOptions = {}) {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });

  function Wrapper({ children }: { children: ReactNode }) {
    return (
      <QueryClientProvider client={queryClient}>
        <MemoryRouter>
          <I18nextProvider i18n={testI18n}>
            <AuthContext.Provider value={buildAuthValue(permissions)}>{children}</AuthContext.Provider>
          </I18nextProvider>
        </MemoryRouter>
      </QueryClientProvider>
    );
  }

  return render(ui, { wrapper: Wrapper });
}

export const ALL_TICKET_PERMISSIONS = [
  'tickets.view.any',
  'tickets.update',
  'tickets.assign',
  'tickets.claim',
  'tickets.transfer.agent',
  'tickets.transfer.department',
  'tickets.status.change',
  'tickets.reopen',
  'tickets.spam.mark',
  'tickets.spam.restore',
  'tickets.link',
  'tickets.history.view',
  'ticket.message.view',
  'ticket.message.send',
  'ticket.message.internal_view',
  'ticket.message.internal_write',
  'ticket.message.retry',
  'workspace.ticket.watchers.view',
  'attachments.upload',
  'customers.view',
  'customers.timeline.view',
];
