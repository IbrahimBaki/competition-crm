import axios, { AxiosError } from 'axios';
import { MalformedEnvelopeError } from './envelope';

export type NormalisedApiError = {
  status: number;
  code: string | null;
  message: string;
  fieldErrors: Record<string, string[]>;
  requestId: string | null;
  kind:
    | 'validation'
    | 'unauthenticated'
    | 'forbidden'
    | 'not_found'
    | 'conflict'
    | 'rate_limited'
    | 'csrf'
    | 'server'
    | 'network'
    | 'unknown'
    | 'two_factor_required'
    | 'account_deactivated'
    | 'cancelled'
    | 'client_error';
};

export function isApiError(value: unknown): value is NormalisedApiError {
  if (!value || typeof value !== 'object') return false;
  return (
    'status' in value &&
    'code' in value &&
    'message' in value &&
    'fieldErrors' in value &&
    'kind' in value
  );
}

function extractErrorCode(response: any): string | null {
  if (
    response &&
    typeof response === 'object' &&
    'error' in response &&
    response.error &&
    typeof response.error === 'object' &&
    'code' in response.error
  ) {
    return response.error.code;
  }
  return null;
}

function extractFieldErrors(response: any): Record<string, string[]> {
  if (
    response &&
    typeof response === 'object' &&
    'error' in response &&
    response.error &&
    typeof response.error === 'object' &&
    'field_errors' in response.error &&
    typeof response.error.field_errors === 'object' &&
    response.error.field_errors !== null
  ) {
    return response.error.field_errors as Record<string, string[]>;
  }
  return {};
}

function extractRequestId(
  response: any,
  headers: Record<string, string | string[] | undefined>
): string | null {
  if (
    response &&
    typeof response === 'object' &&
    'error' in response &&
    response.error &&
    typeof response.error === 'object' &&
    'request_id' in response.error &&
    typeof response.error.request_id === 'string'
  ) {
    return response.error.request_id;
  }

  const headerValue = headers['x-request-id'];
  if (typeof headerValue === 'string') {
    return headerValue;
  }
  if (Array.isArray(headerValue)) {
    return headerValue[0] ?? null;
  }

  return null;
}

function normaliseErrorMessage(response: any, status: number): string {
  if (
    response &&
    typeof response === 'object' &&
    'error' in response &&
    response.error &&
    typeof response.error === 'object' &&
    'message' in response.error &&
    typeof response.error.message === 'string'
  ) {
    return response.error.message;
  }

  const statusMessages: Record<number, string> = {
    400: 'Bad request',
    401: 'Unauthenticated',
    403: 'Forbidden',
    404: 'Not found',
    409: 'Conflict',
    419: 'Session expired, please refresh',
    422: 'Validation failed',
    429: 'Too many requests',
    500: 'Server error',
    503: 'Service unavailable',
  };

  return statusMessages[status] ?? 'An error occurred';
}

function determineErrorKind(status: number, code: string | null): NormalisedApiError['kind'] {
  if (code) {
    const codeMap: Record<string, NormalisedApiError['kind']> = {
      'VALIDATION_FAILED': 'validation',
      'VALIDATION_FAILED_VALIDATION': 'validation',
      'UNAUTHENTICATED': 'unauthenticated',
      'UNAUTHORIZED': 'forbidden',
      'NOT_FOUND': 'not_found',
      'CONFLICT': 'conflict',
      'RATE_LIMITED': 'rate_limited',
      'TWO_FACTOR_REQUIRED': 'two_factor_required',
      'ACCOUNT_DEACTIVATED': 'account_deactivated',
    };

    const mappedKind = codeMap[code];
    if (mappedKind) {
      return mappedKind;
    }
  }

  const statusKindMap: Record<number, NormalisedApiError['kind']> = {
    422: 'validation',
    401: 'unauthenticated',
    403: 'forbidden',
    404: 'not_found',
    409: 'conflict',
    419: 'csrf',
    429: 'rate_limited',
  };

  if (status >= 500) {
    return 'server';
  }

  return statusKindMap[status] || 'unknown';
}

export function normaliseApiError(error: unknown): NormalisedApiError {
  // A request aborted by the caller (e.g. TanStack Query cancelling a
  // superseded fetch, or React StrictMode's dev-only double-mount) is not a
  // failure — a fresh request is already in flight. Never surface this as
  // "Network error".
  if (axios.isCancel(error) || (error instanceof AxiosError && error.code === 'ERR_CANCELED')) {
    return {
      status: 0,
      code: null,
      message: 'Request cancelled',
      fieldErrors: {},
      requestId: null,
      kind: 'cancelled',
    };
  }

  // The HTTP round-trip succeeded but the response body didn't match the
  // expected envelope shape — a client-side parsing problem, not a network
  // failure.
  if (error instanceof MalformedEnvelopeError) {
    return {
      status: 0,
      code: null,
      message: 'Received an unexpected response shape from the server.',
      fieldErrors: {},
      requestId: null,
      kind: 'client_error',
    };
  }

  // Handle network errors
  if (!(error instanceof AxiosError)) {
    return {
      status: 0,
      code: null,
      message: 'Network error',
      fieldErrors: {},
      requestId: null,
      kind: 'network',
    };
  }

  const status = error.status ?? error.response?.status ?? 0;
  const response = error.response?.data;
  const headers = error.response?.headers ?? {};

  const code = extractErrorCode(response);
  const fieldErrors = extractFieldErrors(response);
  const requestId = extractRequestId(response, headers as Record<string, string | string[]>);
  const message = normaliseErrorMessage(response, status);
  const kind = determineErrorKind(status, code);

  return {
    status,
    code,
    message,
    fieldErrors,
    requestId,
    kind,
  };
}
