import { createContext, useContext, ReactNode } from 'react';
import { getPortalSession, getPortalUser, clearPortalSession, PortalUser } from './portalSession';

interface PortalAuthContextType {
  user: PortalUser | null;
  isAuthenticated: boolean;
  logout: () => void;
}

const PortalAuthContext = createContext<PortalAuthContextType | null>(null);

export function PortalAuthProvider({ children }: { children: ReactNode }) {
  const session = getPortalSession();
  const user = getPortalUser();
  const isAuthenticated = !!session && !!user;

  const logout = () => {
    clearPortalSession();
    window.location.href = '/portal/login';
  };

  return (
    <PortalAuthContext.Provider value={{ user: user || null, isAuthenticated, logout }}>
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
