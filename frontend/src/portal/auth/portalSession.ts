// Separate from staff auth session (frontend/src/auth/session.ts)
// Portal uses 'auth:portal' guard; staff uses 'auth:sanctum'

const PORTAL_SESSION_KEY = '__portal_session__';
const PORTAL_USER_KEY = '__portal_user__';

export interface PortalSession {
  token: string;
  expiresAt: number;
}

export interface PortalUser {
  id: string;
  email: string;
  name: string;
}

export function getPortalSession(): PortalSession | null {
  try {
    const session = localStorage.getItem(PORTAL_SESSION_KEY);
    if (!session) return null;
    const parsed = JSON.parse(session);
    if (parsed.expiresAt < Date.now()) {
      clearPortalSession();
      return null;
    }
    return parsed;
  } catch {
    return null;
  }
}

export function setPortalSession(token: string, expiresAt: number): void {
  localStorage.setItem(PORTAL_SESSION_KEY, JSON.stringify({ token, expiresAt }));
}

export function getPortalUser(): PortalUser | null {
  try {
    const user = localStorage.getItem(PORTAL_USER_KEY);
    return user ? JSON.parse(user) : null;
  } catch {
    return null;
  }
}

export function setPortalUser(user: PortalUser): void {
  localStorage.setItem(PORTAL_USER_KEY, JSON.stringify(user));
}

export function clearPortalSession(): void {
  localStorage.removeItem(PORTAL_SESSION_KEY);
  localStorage.removeItem(PORTAL_USER_KEY);
}
