import React from 'react';
import { UseQueryResult } from '@tanstack/react-query';
import { LoadingState } from './states/LoadingState';
import { EmptyState } from './states/EmptyState';
import { ErrorState } from './states/ErrorState';
import { isApiError, NormalisedApiError } from '@/api/http/errors';

interface AsyncBoundaryProps<T> {
  query: UseQueryResult<T, unknown>;
  isEmpty?: (data: T) => boolean;
  loading?: React.ReactNode;
  empty?: React.ReactNode;
  error?: React.ReactNode;
  children: (data: T) => React.ReactNode;
}

export function AsyncBoundary<T>({
  query,
  isEmpty = () => false,
  loading = <LoadingState />,
  empty = <EmptyState title="No data" />,
  error,
  children,
}: AsyncBoundaryProps<T>) {
  if (query.isLoading) {
    return <>{loading}</>;
  }

  // A cancelled request (superseded fetch, StrictMode dev double-mount) is
  // not a real failure — a fresh request is already in flight. Keep showing
  // the loading state instead of flashing an error.
  if (query.isError && isApiError(query.error) && query.error.kind === 'cancelled') {
    return <>{loading}</>;
  }

  if (query.isError) {
    const errorContent = error || (
      <ErrorState
        error={
          query.error instanceof Error
            ? ({
                status: 0,
                code: null,
                message: query.error.message,
                fieldErrors: {},
                requestId: null,
                kind: 'unknown',
              } as NormalisedApiError)
            : (query.error as NormalisedApiError)
        }
        onRetry={() => query.refetch()}
      />
    );
    return <>{errorContent}</>;
  }

  if (!query.data || isEmpty(query.data)) {
    return <>{empty}</>;
  }

  return <>{children(query.data)}</>;
}
