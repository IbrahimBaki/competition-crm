import { QueryClient } from '@tanstack/react-query';
import { normaliseApiError } from './http/errors';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 30_000,
      refetchOnWindowFocus: false,
      retry: (failureCount, error) => {
        try {
          const e = normaliseApiError(error);
          if (
            [
              'unauthenticated',
              'forbidden',
              'validation',
              'not_found',
              'csrf',
              'two_factor_required',
              'account_deactivated',
            ].includes(e.kind)
          ) {
            return false;
          }
          return failureCount < 2;
        } catch {
          return failureCount < 2;
        }
      },
    },
    mutations: { retry: false },
  },
});
