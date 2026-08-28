import React, { createContext, useState, useEffect, useCallback } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { httpClient } from '@/api/http/client';
import { ensureCsrfCookie } from '@/api/http/csrf';
import { unwrap } from '@/api/http/envelope';
import { normaliseApiError } from '@/api/http/errors';
import { setSession, on, User } from './session';

export type AuthStatus = 'loading' | 'authenticated' | 'unauthenticated' | 'two_factor_required';

interface AuthContextType {
  status: AuthStatus;
  user: User | null;
  permissions: string[];
  login: (email: string, password: string) => Promise<void>;
  completeTwoFactor: (code: string) => Promise<void>;
  logout: () => Promise<void>;
  reload: () => Promise<void>;
}

export const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function useAuth(): AuthContextType {
  const context = React.useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
}

interface AuthProviderProps {
  children: React.ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [status, setStatus] = useState<AuthStatus>('loading');
  const [user, setUser] = useState<User | null>(null);
  const queryClient = useQueryClient();

  // Bootstrap session on mount
  useEffect(() => {
    const bootstrap = async () => {
      try {
        await ensureCsrfCookie();
        const response = await httpClient.get('/auth/me');
        const currentUser = unwrap<User>(response.data);
        setSession(currentUser);
        setUser(currentUser);
        setStatus('authenticated');
      } catch (error) {
        const normalised = normaliseApiError(error);
        if (normalised.kind === 'unauthenticated') {
          setSession(null);
          setUser(null);
          setStatus('unauthenticated');
        } else {
          console.error('Session bootstrap failed:', normalised);
          setStatus('unauthenticated');
        }
      }
    };

    bootstrap();
  }, []);

  // Subscribe to session:expired event
  useEffect(() => {
    return on('session:expired', () => {
      setSession(null);
      setUser(null);
      queryClient.clear();
      setStatus('unauthenticated');
      // Redirect handled by router
      window.location.href = '/login?reason=session_expired';
    });
  }, [queryClient]);

  // Handle focus/visibility changes to revalidate session
  useEffect(() => {
    let focusTimeout: ReturnType<typeof setTimeout>;
    let lastRecheck = Date.now();

    const handleFocus = () => {
      const now = Date.now();
      if (now - lastRecheck >= 30000) {
        // At most once per 30s
        lastRecheck = now;
        if (status === 'authenticated') {
          // Silently revalidate session
          (async () => {
            try {
              const response = await httpClient.get('/auth/me');
              const currentUser = unwrap<User>(response.data);
              setSession(currentUser);
              setUser(currentUser);
            } catch {
              // Session expired, will be caught by mutator and redirected
            }
          })();
        }
      }
    };

    const handleVisibilityChange = () => {
      if (!document.hidden) {
        focusTimeout = setTimeout(handleFocus, 100);
      }
    };

    window.addEventListener('focus', handleFocus);
    document.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      window.removeEventListener('focus', handleFocus);
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      clearTimeout(focusTimeout);
    };
  }, [status]);

  const login = useCallback(async (email: string, password: string) => {
    try {
      await ensureCsrfCookie();

      const response = await httpClient.post('/auth/login', { email, password });

      // Check if two-factor is required
      if (response.data?.meta?.two_factor_required) {
        setStatus('two_factor_required');
        return;
      }

      // Fetch current user to get latest permissions
      const meResponse = await httpClient.get('/auth/me');
      const currentUser = unwrap<User>(meResponse.data);

      setSession(currentUser);
      setUser(currentUser);
      setStatus('authenticated');
    } catch (error) {
      const normalised = normaliseApiError(error);

      // Check for two-factor required error codes
      if (
        normalised.code === 'TWO_FACTOR_REQUIRED' ||
        normalised.kind === 'two_factor_required'
      ) {
        setStatus('two_factor_required');
        throw normalised;
      }

      throw normalised;
    }
  }, []);

  const completeTwoFactor = useCallback(async (code: string) => {
    try {
      await httpClient.post('/auth/two-factor/challenge', { code });

      // Fetch current user
      const response = await httpClient.get('/auth/me');
      const currentUser = unwrap<User>(response.data);

      setSession(currentUser);
      setUser(currentUser);
      setStatus('authenticated');
    } catch (error) {
      throw normaliseApiError(error);
    }
  }, []);

  const logout = useCallback(async () => {
    try {
      await httpClient.post('/auth/logout');
    } catch {
      // Ignore errors on logout
    } finally {
      setSession(null);
      setUser(null);
      setStatus('unauthenticated');
      queryClient.clear();
      window.location.href = '/login';
    }
  }, [queryClient]);

  const reload = useCallback(async () => {
    try {
      const response = await httpClient.get('/auth/me');
      const currentUser = unwrap<User>(response.data);
      setSession(currentUser);
      setUser(currentUser);
      setStatus('authenticated');
    } catch {
      setSession(null);
      setUser(null);
      setStatus('unauthenticated');
    }
  }, []);

  // Show loading state during bootstrap
  if (status === 'loading') {
    return (
      <div className="flex items-center justify-center h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto mb-4" />
          <p className="text-gray-600">Loading...</p>
        </div>
      </div>
    );
  }

  return (
    <AuthContext.Provider
      value={{
        status,
        user,
        permissions: user?.permission_keys ?? [],
        login,
        completeTwoFactor,
        logout,
        reload,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}
