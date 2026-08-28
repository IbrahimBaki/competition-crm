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
    const listeners = eventListeners['session:changed'];
    if (listeners) {
      listeners.forEach((cb) => cb(user));
    }
  }
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

export function emit(event: 'session:expired' | 'session:changed', data?: any): void {
  const listeners = eventListeners[event];
  if (listeners) {
    listeners.forEach((cb) => cb(data));
  }
}
