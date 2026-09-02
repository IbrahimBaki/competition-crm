import { AxiosRequestConfig } from 'axios';
import { httpClient } from './client';
import { normaliseApiError } from './errors';
import { unwrap, unwrapPage, MalformedEnvelopeError } from './envelope';
import { ensureCsrfCookie } from './csrf';
import { expireSession, recoverSession } from '@/auth/session';
import { getPortalSession } from '@/portal/auth/portalSession';

// Track in-flight recovery to deduplicate concurrent 401s
let recoveryInFlight: Promise<boolean> | null = null;

// Exempt endpoints from 401 recovery to prevent infinite loops
const EXEMPT_FROM_RECOVERY = ['/auth/me', '/auth/login'];

async function shouldRetryOn401(config: AxiosRequestConfig): Promise<boolean> {
  const url = config.url ?? '';
  return !EXEMPT_FROM_RECOVERY.some((exempt) => url.includes(exempt));
}

export const apiRequest = async <T>(
  config: AxiosRequestConfig,
  options?: AxiosRequestConfig
): Promise<T> => {
  const mergedConfig = { ...config, ...options };

  // 1. Attach locale header
  const locale = localStorage.getItem('locale') ?? 'en';
  mergedConfig.headers = {
    ...mergedConfig.headers,
    'Accept-Language': locale,
  };

  // Portal accounts use a separate Sanctum bearer guard. Never attach this
  // token to staff endpoints: Laravel's portal.deny middleware intentionally
  // rejects portal credentials on the staff API surface.
  if ((mergedConfig.url ?? '').startsWith('/portal/')) {
    const portalToken = getPortalSession()?.token;
    if (portalToken) mergedConfig.headers.Authorization = `Bearer ${portalToken}`;
  }

  // 2. Attach idempotency key if provided
  if (mergedConfig.headers?.['Idempotency-Key']) {
    // Already set by caller, preserve it
  }

  try {
    // 3. Issue the request
    const response = await httpClient(mergedConfig);

    // 4. On success, unwrap the response
    if (response.status === 204 || response.data === '' || response.data === undefined) return undefined as T;
    const isList = response.data?.meta?.page !== undefined;
    return (isList ? unwrapPage(response.data) : unwrap(response.data)) as T;
  } catch (error) {
    // 5. Recovery ladder
    if (error instanceof MalformedEnvelopeError) {
      throw normaliseApiError(error);
    }

    // 419 (CSRF): Refresh and retry once
    if (error instanceof Error && 'response' in error) {
      const axiosError = error as any;
      if (axiosError.response?.status === 419) {
        if (!axiosError.config?.__retried) {
          await ensureCsrfCookie();
          const retryConfig = { ...mergedConfig, __retried: true };
          const response = await httpClient(retryConfig);
          if (response.status === 204 || response.data === '' || response.data === undefined) return undefined as T;
          const isList = response.data?.meta?.page !== undefined;
          return (isList ? unwrapPage(response.data) : unwrap(response.data)) as T;
        }
        // Second 419 is terminal
      }

      // 401 (session expired): Try recovery once, deduplicated
      if (axiosError.response?.status === 401 && !(mergedConfig.url ?? '').startsWith('/portal/')) {
        if (await shouldRetryOn401(mergedConfig)) {
          // Deduplicate recovery across concurrent 401s
          recoveryInFlight ??= recoverSession();

          try {
            const recovered = await recoveryInFlight;
            if (recovered) {
              // Session recovered, retry the original request
              const response = await httpClient(mergedConfig);
              if (response.status === 204 || response.data === '' || response.data === undefined) return undefined as T;
              const isList = response.data?.meta?.page !== undefined;
              return (isList ? unwrapPage(response.data) : unwrap(response.data)) as T;
            }
          } finally {
            recoveryInFlight = null;
          }

          // Recovery failed. AuthProvider owns cleanup and redirect after this
          // typed, in-memory signal; expireSession is idempotent per session.
          expireSession();
        }
      }
    }

    // Re-throw as normalised error
    throw normaliseApiError(error);
  }
};
