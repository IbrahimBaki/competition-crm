import { createContext, useCallback, useContext, useEffect, useState, ReactNode } from 'react';
import { getPortalSession, getPortalUser, clearPortalSession, setPortalSession, setPortalUser, PortalUser } from './portalSession';
import { apiRequest } from '@/api/http/mutator';
import { httpClient } from '@/api/http/client';
import { normaliseApiError } from '@/api/http/errors';

interface PortalAuthContextType {
  user: PortalUser | null;
  isAuthenticated: boolean;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
}

const PortalAuthContext = createContext<PortalAuthContextType | null>(null);

export function PortalAuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<PortalUser | null>(() => getPortalUser());
  const [loading, setLoading] = useState(() => Boolean(getPortalSession()));

  const loadUser = useCallback(async () => {
    const result = await apiRequest<PortalUser>({ url: '/portal/me', method: 'GET' });
    const portalUser: PortalUser = {
      id: String((result as any).uuid ?? (result as any).id ?? ''),
      email: String((result as any).email ?? ''),
      name: String((result as any).name ?? (result as any).email ?? ''),
    };
    setPortalUser(portalUser);
    setUser(portalUser);
  }, []);

  useEffect(() => {
    if (!getPortalSession()) { setLoading(false); return; }
    loadUser().catch(() => { clearPortalSession(); setUser(null); }).finally(() => setLoading(false));
  }, [loadUser]);

  const login = useCallback(async (email: string, password: string) => {
    try {
      const result = await apiRequest<{ token?: string }>({ url: '/portal/auth/login', method: 'POST', data: { email, password } });
      if (!result.token) throw new Error('Portal login returned no token');
      setPortalSession(result.token);
      await loadUser();
    } catch (error) {
      clearPortalSession();
      throw normaliseApiError(error);
    }
  }, [loadUser]);

  const logout = useCallback(async () => {
    const token = getPortalSession()?.token;
    try { if (token) await httpClient.post('/portal/auth/logout', undefined, { headers: { Authorization: `Bearer ${token}` } }); } finally { clearPortalSession(); setUser(null); window.location.href = '/portal/login'; }
  }, []);

  return (
    <PortalAuthContext.Provider value={{ user, isAuthenticated: Boolean(user), loading, login, logout }}>
      {children}
    </PortalAuthContext.Provider>
  );
}

export function usePortalAuth() {
  const context = useContext(PortalAuthContext);
  if (!context) {
    throw new Error('usePortalAuth must be used within PortalAuthProvider');
  }
  return context;
}
