import { httpClient } from '@/api/http/client';
import { ensureCsrfCookie } from '@/api/http/csrf';
import { unwrap } from '@/api/http/envelope';

export interface User {
  id: string;
  email: string;
  name: string;
  locale: string;
  available_locales: string[];
  permission_keys: string[];
  primary_branch_id: string | null;
  department_ids: string[];
}

let currentSession: User | null = null;
let recoveryInFlight: Promise<boolean> | null = null;
let sessionExpirySignalled = false;

const eventListeners: Record<string, Set<(data?: any) => void>> = {
  'session:expired': new Set(),
  'session:changed': new Set(),
};

export function getSession(): User | null {
  return currentSession;
}

export function setSession(user: User | null): void {
  currentSession = user;
  if (user) {
    // A new authenticated session starts a new expiry lifecycle.
    sessionExpirySignalled = false;
    emit('session:changed', user);
  }
}

/**
 * Emits the one canonical staff-session expiry signal for the current
 * authenticated session. AuthProvider owns the resulting cleanup/redirect.
 */
export function expireSession(): boolean {
  if (sessionExpirySignalled) return false;

  sessionExpirySignalled = true;
  emit('session:expired');
  return true;
}

export async function recoverSession(): Promise<boolean> {
  // Deduplicate concurrent recovery attempts
  recoveryInFlight ??= (async () => {
    try {
      await ensureCsrfCookie();
      const response = await httpClient.get('/auth/me');
      const user = unwrap<User>(response.data);
      setSession(user);
      return true;
    } catch {
      setSession(null);
      return false;
    } finally {
      recoveryInFlight = null;
    }
  })();

  return recoveryInFlight;
}

export function on(
  event: 'session:expired' | 'session:changed',
  callback: (data?: any) => void
): () => void {
  if (!eventListeners[event]) {
    eventListeners[event] = new Set();
  }

  const listeners = eventListeners[event];
  listeners.add(callback);

  // Return unsubscribe function
  return () => {
    listeners.delete(callback);
  };
}

function emit(event: 'session:expired' | 'session:changed', data?: unknown): void {
  const listeners = eventListeners[event];
  if (listeners) {
    listeners.forEach((cb) => cb(data));
  }
}
