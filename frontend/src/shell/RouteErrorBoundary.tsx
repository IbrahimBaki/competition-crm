import { useRouteError } from 'react-router-dom';
import { ErrorState, ForbiddenState, NotFoundState } from './states';
import { isApiError } from '@/api/http/errors';

export function RouteErrorBoundary() {
  const error = useRouteError();

  if (isApiError(error)) {
    if (error.kind === 'forbidden') {
      return <ForbiddenState />;
    }
    if (error.kind === 'not_found') {
      return <NotFoundState />;
    }
    return <ErrorState error={error} />;
  }

  console.error('Uncaught error:', error);

  return (
    <ErrorState
      error={{
        status: 500,
        code: null,
        message: error instanceof Error ? error.message : 'An unexpected error occurred',
        fieldErrors: {},
        requestId: null,
        kind: 'unknown',
      }}
    />
  );
}
