import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { act, render, screen, waitFor } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { AuthProvider, useAuth } from '../AuthProvider';
import { expireSession, setSession, type User } from '../session';

const mocks = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn(),
  ensureCsrfCookie: vi.fn(),
}));

vi.mock('@/api/http/client', () => ({
  httpClient: { get: mocks.get, post: mocks.post },
}));
vi.mock('@/api/http/csrf', () => ({ ensureCsrfCookie: mocks.ensureCsrfCookie }));

const user: User = {
  id: 'staff-1',
  email: 'staff@example.test',
  name: 'Staff member',
  locale: 'en',
  available_locales: ['en'],
  permission_keys: [],
  primary_branch_id: null,
  department_ids: [],
};

function currentUserResponse() {
  return { data: { data: user } };
}

function unauthenticatedError() {
  return new AxiosError(
    'Unauthenticated',
    undefined,
    undefined,
    undefined,
    { status: 401, data: { error: { message: 'Unauthenticated' } } } as never
  );
}

function Probe() {
  const { status, user: authenticatedUser } = useAuth();
  return <output data-testid="auth-state">{`${status}:${authenticatedUser?.id ?? 'none'}`}</output>;
}

function renderProvider() {
  const queryClient = new QueryClient();
  const clear = vi.spyOn(queryClient, 'clear');
  const result = render(
    <QueryClientProvider client={queryClient}>
      <AuthProvider><Probe /></AuthProvider>
    </QueryClientProvider>
  );
  return { ...result, clear };
}

afterEach(() => {
  vi.restoreAllMocks();
  setSession(user);
});

describe('AuthProvider session expiry handling', () => {
  it('clears authenticated state and the Query cache in response to the canonical expiry signal', async () => {
    mocks.ensureCsrfCookie.mockResolvedValue(undefined);
    mocks.get.mockResolvedValue(currentUserResponse());
    const consoleError = vi.spyOn(console, 'error').mockImplementation(() => undefined);
    const { clear } = renderProvider();

    await screen.findByText('authenticated:staff-1');
    act(() => { expireSession(); });

    await waitFor(() => expect(screen.getByTestId('auth-state')).toHaveTextContent('unauthenticated:none'));
    expect(clear).toHaveBeenCalledTimes(1);
    consoleError.mockRestore();
  });

  it('uses the canonical expiry signal when focus revalidation receives a staff 401', async () => {
    mocks.ensureCsrfCookie.mockResolvedValue(undefined);
    mocks.get.mockResolvedValueOnce(currentUserResponse()).mockRejectedValueOnce(unauthenticatedError());
    const now = vi.spyOn(Date, 'now').mockReturnValue(0);
    const consoleError = vi.spyOn(console, 'error').mockImplementation(() => undefined);
    const { clear } = renderProvider();

    await screen.findByText('authenticated:staff-1');
    await waitFor(() => expect(mocks.get).toHaveBeenCalledTimes(1));
    await act(async () => { await new Promise((resolve) => setTimeout(resolve, 0)); });
    now.mockReturnValue(30_001);
    await act(async () => {
      window.dispatchEvent(new Event('focus'));
      await Promise.resolve();
    });

    await waitFor(() => expect(screen.getByTestId('auth-state')).toHaveTextContent('unauthenticated:none'));
    expect(clear).toHaveBeenCalledTimes(1);
    consoleError.mockRestore();
  });

  it('unsubscribes from the expiry signal when unmounted', async () => {
    mocks.ensureCsrfCookie.mockResolvedValue(undefined);
    mocks.get.mockResolvedValue(currentUserResponse());
    const { clear, unmount } = renderProvider();

    await screen.findByText('authenticated:staff-1');
    unmount();
    setSession({ ...user, id: 'staff-2' });
    expireSession();

    expect(clear).not.toHaveBeenCalled();
  });
});
