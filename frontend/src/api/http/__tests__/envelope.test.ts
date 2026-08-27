import { describe, it, expect } from 'vitest';
import { unwrap, unwrapPage, MalformedEnvelopeError } from '../envelope';

describe('envelope', () => {
  describe('unwrap', () => {
    it('returns the data for a single-resource response', () => {
      const response = {
        data: { id: '123', name: 'Test' },
        meta: { request_id: 'abc' },
      };
      const result = unwrap(response);
      expect(result).toEqual({ id: '123', name: 'Test' });
    });

    it('returns undefined when data is undefined', () => {
      const response = {
        data: undefined,
        meta: { request_id: 'abc' },
      };
      const result = unwrap(response);
      expect(result).toBeUndefined();
    });
  });

  describe('unwrapPage', () => {
    it('returns items and pagination meta for a list response', () => {
      const response = {
        data: [{ id: '1', name: 'Item 1' }],
        meta: {
          request_id: 'abc',
          page: 1,
          per_page: 25,
          total: 100,
          total_pages: 4,
        },
      };
      const result = unwrapPage(response);
      expect(result.items).toEqual([{ id: '1', name: 'Item 1' }]);
      expect(result.meta.page).toBe(1);
      expect(result.meta.total).toBe(100);
    });

    it('throws MalformedEnvelopeError when pagination meta is invalid', () => {
      const response = {
        data: [{ id: '1' }],
        meta: { request_id: 'abc' }, // missing pagination fields
      };
      expect(() => unwrapPage(response)).toThrow(MalformedEnvelopeError);
    });
  });
});
