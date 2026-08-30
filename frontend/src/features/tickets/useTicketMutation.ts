import { useState } from 'react';
import {
  useQueryClient,
  type UseMutationOptions,
  type UseMutationResult,
} from '@tanstack/react-query';
import { normaliseApiError, type NormalisedApiError } from '@/api/http/errors';
import {
  getGetTicketQueryKey,
  getGetTicketHistoryQueryKey,
} from '@/api/generated/ticketing/ticketing';

type MutationHook<TData, TVars> = (
  options?: UseMutationOptions<TData, unknown, TVars>
) => UseMutationResult<TData, unknown, TVars>;

export type UseTicketMutationResult<TData, TVars> = UseMutationResult<TData, unknown, TVars> & {
  conflict: NormalisedApiError | null;
  fieldErrors: Record<string, string[]>;
  clearConflict: () => void;
  reloadLatest: () => void;
};

/**
 * Centralises what every ticket-detail mutation needs: conflict (409)
 * detection, error-envelope unwrapping, field-error extraction for forms,
 * and invalidating the detail + history queries after a change. Every
 * mutating control on the detail screen goes through this so a 409 always
 * surfaces the same way (see ConflictBanner.tsx).
 */
export function useTicketMutation<TData, TVars>(
  useMutationHook: MutationHook<TData, TVars>,
  ticketId: string,
  extra?: UseMutationOptions<TData, unknown, TVars>
): UseTicketMutationResult<TData, TVars> {
  const queryClient = useQueryClient();
  const [conflict, setConflict] = useState<NormalisedApiError | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  const invalidate = () => {
    queryClient.invalidateQueries({ queryKey: getGetTicketQueryKey(ticketId) });
    queryClient.invalidateQueries({ queryKey: getGetTicketHistoryQueryKey(ticketId) });
  };

  const mutation = useMutationHook({
    ...extra,
    onMutate: (vars, context) => {
      setConflict(null);
      setFieldErrors({});
      return extra?.onMutate?.(vars, context);
    },
    onSuccess: (data, vars, onMutateResult, context) => {
      invalidate();
      extra?.onSuccess?.(data, vars, onMutateResult, context);
    },
    onError: (error, vars, onMutateResult, context) => {
      const normalised = normaliseApiError(error);
      if (normalised.kind === 'conflict') {
        setConflict(normalised);
      } else if (normalised.kind === 'validation') {
        setFieldErrors(normalised.fieldErrors);
      }
      extra?.onError?.(error, vars, onMutateResult, context);
    },
  });

  const reloadLatest = () => {
    setConflict(null);
    queryClient.invalidateQueries({ queryKey: getGetTicketQueryKey(ticketId) });
  };

  return {
    ...mutation,
    conflict,
    fieldErrors,
    clearConflict: () => setConflict(null),
    reloadLatest,
  };
}
