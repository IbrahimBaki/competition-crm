import { describe, it, expect } from 'vitest';
import { hasPermission, hasAnyPermission, hasAllPermissions, PERMISSIONS } from '../permissions';

describe('permissions', () => {
  const grantedKeys = [PERMISSIONS.TICKETS_VIEW_ANY, PERMISSIONS.ADMIN_ROLES_MANAGE];

  describe('hasPermission', () => {
    it('returns true when permission is granted', () => {
      expect(hasPermission(grantedKeys, PERMISSIONS.TICKETS_VIEW_ANY)).toBe(true);
    });

    it('returns false when permission is not granted', () => {
      expect(hasPermission(grantedKeys, PERMISSIONS.TICKETS_CREATE)).toBe(false);
    });

    it('returns false for an unknown permission key', () => {
      expect(hasPermission(grantedKeys, 'unknown.permission' as any)).toBe(false);
    });

    it('returns false with an empty granted list', () => {
      expect(hasPermission([], PERMISSIONS.TICKETS_VIEW_ANY)).toBe(false);
    });
  });

  describe('hasAnyPermission', () => {
    it('returns true when at least one permission is granted', () => {
      expect(
        hasAnyPermission(grantedKeys, [
          PERMISSIONS.TICKETS_VIEW_ANY,
          PERMISSIONS.TICKETS_CREATE,
        ])
      ).toBe(true);
    });

    it('returns false when none of the permissions are granted', () => {
      expect(
        hasAnyPermission(grantedKeys, [
          PERMISSIONS.TICKETS_CREATE,
          PERMISSIONS.TICKETS_UPDATE,
        ])
      ).toBe(false);
    });

    it('returns false with an empty granted list', () => {
      expect(hasAnyPermission([], [PERMISSIONS.TICKETS_VIEW_ANY])).toBe(false);
    });
  });

  describe('hasAllPermissions', () => {
    it('returns true when all permissions are granted', () => {
      expect(
        hasAllPermissions(grantedKeys, [
          PERMISSIONS.TICKETS_VIEW_ANY,
          PERMISSIONS.ADMIN_ROLES_MANAGE,
        ])
      ).toBe(true);
    });

    it('returns false when not all permissions are granted', () => {
      expect(
        hasAllPermissions(grantedKeys, [
          PERMISSIONS.TICKETS_VIEW_ANY,
          PERMISSIONS.TICKETS_VIEW_OWN,
        ])
      ).toBe(false);
    });

    it('returns true with an empty required list', () => {
      expect(hasAllPermissions(grantedKeys, [])).toBe(true);
    });
  });
});
