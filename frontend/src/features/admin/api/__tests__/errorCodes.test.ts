import { describe, it, expect } from 'vitest';
import en from '@/i18n/en.json';
import ar from '@/i18n/ar.json';
import { ADMIN_ERROR_MESSAGE_KEYS, adminErrorMessageKey } from '../errorCodes';

describe('admin error code mapping', () => {
  it('every mapped code resolves to a key that exists in both en.json and ar.json', () => {
    const enKeys = new Set(Object.keys(en));
    const arKeys = new Set(Object.keys(ar));

    for (const [code, key] of Object.entries(ADMIN_ERROR_MESSAGE_KEYS)) {
      expect(enKeys.has(key), `en.json is missing "${key}" for code "${code}"`).toBe(true);
      expect(arKeys.has(key), `ar.json is missing "${key}" for code "${code}"`).toBe(true);
    }
  });

  it('resolves a known code to its mapped key', () => {
    expect(adminErrorMessageKey('branch.has_active_departments')).toBe(
      'admin.errors.branch_has_active_departments'
    );
  });

  it('falls back to admin.errors.unexpected for an unknown or missing code', () => {
    expect(adminErrorMessageKey('some.unknown.code')).toBe('admin.errors.unexpected');
    expect(adminErrorMessageKey(undefined)).toBe('admin.errors.unexpected');
    expect(adminErrorMessageKey(null)).toBe('admin.errors.unexpected');
  });

  it('admin.errors.unexpected itself exists in both locale files', () => {
    expect((en as Record<string, string>)['admin.errors.unexpected']).toBeTruthy();
    expect((ar as Record<string, string>)['admin.errors.unexpected']).toBeTruthy();
  });
});
