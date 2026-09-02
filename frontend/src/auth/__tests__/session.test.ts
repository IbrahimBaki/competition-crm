import { afterEach, describe, expect, it, vi } from 'vitest';
import { expireSession, on, setSession, type User } from '../session';

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

afterEach(() => {
  // Starting an authenticated session resets the per-session expiry latch.
  setSession(user);
});

describe('staff session expiry signal', () => {
  it('emits once per authenticated session and resets after a new session starts', () => {
    const listener = vi.fn();
    const unsubscribe = on('session:expired', listener);
    setSession(user);

    expect(expireSession()).toBe(true);
    expect(expireSession()).toBe(false);
    expect(listener).toHaveBeenCalledTimes(1);

    setSession({ ...user, id: 'staff-2' });
    expect(expireSession()).toBe(true);
    expect(listener).toHaveBeenCalledTimes(2);

    unsubscribe();
  });

  it('removes listeners when unsubscribed', () => {
    const listener = vi.fn();
    const unsubscribe = on('session:expired', listener);
    unsubscribe();
    setSession(user);

    expireSession();

    expect(listener).not.toHaveBeenCalled();
  });
});
