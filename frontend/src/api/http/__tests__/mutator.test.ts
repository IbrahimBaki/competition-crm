import { afterEach, describe, expect, it, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
  httpClient: vi.fn(),
  ensureCsrfCookie: vi.fn(),
  recoverSession: vi.fn(),
  getPortalSession: vi.fn(),
}));

vi.mock('../client', () => ({ httpClient: mocks.httpClient }));
vi.mock('../csrf', () => ({ ensureCsrfCookie: mocks.ensureCsrfCookie }));
vi.mock('@/auth/session', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/auth/session')>();
  return { ...actual, recoverSession: mocks.recoverSession };
});
vi.mock('@/portal/auth/portalSession', () => ({ getPortalSession: mocks.getPortalSession }));

import { apiRequest } from '../mutator';
import { on, setSession, type User } from '@/auth/session';

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

function response(data: unknown) {
  return { status: 200, data: { data } };
}

function httpError(status: number) {
  return Object.assign(new Error(`HTTP ${status}`), {
    response: { status, data: { error: { message: `HTTP ${status}` } } },
    config: {},
  });
}

afterEach(() => {
  vi.clearAllMocks();
  localStorage.clear();
  setSession(user);
});

describe('apiRequest staff-session expiry handling', () => {
  it('signals expiry once after an unrecoverable staff 401', async () => {
    const listener = vi.fn();
    const unsubscribe = on('session:expired', listener);
    setSession(user);
    mocks.httpClient.mockRejectedValueOnce(httpError(401));
    mocks.recoverSession.mockResolvedValue(false);

    await expect(apiRequest({ url: '/tickets', method: 'GET' })).rejects.toBeDefined();

    expect(mocks.recoverSession).toHaveBeenCalledTimes(1);
    expect(listener).toHaveBeenCalledTimes(1);
    unsubscribe();
  });

  it('retries after successful staff recovery without signalling expiry', async () => {
    const listener = vi.fn();
    const unsubscribe = on('session:expired', listener);
    setSession(user);
    mocks.httpClient
      .mockRejectedValueOnce(httpError(401))
      .mockResolvedValueOnce(response({ id: 'ticket-1' }));
    mocks.recoverSession.mockResolvedValue(true);

    await expect(apiRequest<{ id: string }>({ url: '/tickets/ticket-1', method: 'GET' })).resolves.toEqual({ id: 'ticket-1' });

    expect(mocks.recoverSession).toHaveBeenCalledTimes(1);
    expect(listener).not.toHaveBeenCalled();
    unsubscribe();
  });

  it('shares one recovery attempt and one final expiry signal across concurrent staff 401s', async () => {
    const listener = vi.fn();
    const unsubscribe = on('session:expired', listener);
    setSession(user);
    let finishRecovery: (value: boolean) => void = () => undefined;
    const recovery = new Promise<boolean>((resolve) => {
      finishRecovery = resolve;
    });
    mocks.httpClient.mockRejectedValue(httpError(401));
    mocks.recoverSession.mockReturnValue(recovery);

    const requests = [
      apiRequest({ url: '/tickets/one', method: 'GET' }),
      apiRequest({ url: '/tickets/two', method: 'GET' }),
      apiRequest({ url: '/tickets/three', method: 'GET' }),
    ];
    await Promise.resolve();
    finishRecovery(false);

    await expect(Promise.all(requests)).rejects.toBeDefined();

    expect(mocks.recoverSession).toHaveBeenCalledTimes(1);
    expect(listener).toHaveBeenCalledTimes(1);
    unsubscribe();
  });

  it('preserves successful 419 CSRF retry without recovery or expiry', async () => {
    const listener = vi.fn();
    const unsubscribe = on('session:expired', listener);
    setSession(user);
    mocks.httpClient
      .mockRejectedValueOnce(httpError(419))
      .mockResolvedValueOnce(response({ id: 'ticket-1' }));
    mocks.ensureCsrfCookie.mockResolvedValue(undefined);

    await expect(apiRequest<{ id: string }>({ url: '/tickets/ticket-1', method: 'GET' })).resolves.toEqual({ id: 'ticket-1' });

    expect(mocks.ensureCsrfCookie).toHaveBeenCalledTimes(1);
    expect(mocks.recoverSession).not.toHaveBeenCalled();
    expect(listener).not.toHaveBeenCalled();
    unsubscribe();
  });

  it('isolates portal 401s from staff recovery and expiry', async () => {
    const listener = vi.fn();
    const unsubscribe = on('session:expired', listener);
    setSession(user);
    mocks.httpClient.mockRejectedValueOnce(httpError(401));
    mocks.getPortalSession.mockReturnValue({ token: 'portal-token' });

    await expect(apiRequest({ url: '/portal/tickets', method: 'GET' })).rejects.toBeDefined();

    expect(mocks.recoverSession).not.toHaveBeenCalled();
    expect(listener).not.toHaveBeenCalled();
    expect(mocks.httpClient).toHaveBeenCalledWith(expect.objectContaining({
      headers: expect.objectContaining({ Authorization: 'Bearer portal-token' }),
    }));
    unsubscribe();
  });
});
