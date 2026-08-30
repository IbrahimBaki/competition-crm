import { describe, expect, it } from 'vitest';
import { normaliseApiError, type NormalisedApiError } from './errors';

describe('normaliseApiError', () => {
  it('is idempotent for an error already normalised by the HTTP mutator', () => {
    const error: NormalisedApiError = {
      status: 422,
      code: 'validation_failed',
      message: 'Validation failed',
      fieldErrors: { subject: ['The subject field is required.'] },
      requestId: 'request-1',
      kind: 'validation',
    };

    expect(normaliseApiError(error)).toBe(error);
  });
});
