import { describe, expect, it } from 'vitest';
import { hasActiveDescendant, isNavItemActive, isNavPathActive, isWorkspaceActive } from '../navigation.helpers';

describe('isNavPathActive', () => {
  it('matches the exact path and any nested route beneath it', () => {
    expect(isNavPathActive('/tickets', '/tickets')).toBe(true);
    expect(isNavPathActive('/tickets/42', '/tickets')).toBe(true);
    expect(isNavPathActive('/tickets/new', '/tickets')).toBe(true);
  });

  it('does not match a sibling path that merely shares a prefix', () => {
    expect(isNavPathActive('/tickets-archive', '/tickets')).toBe(false);
  });

  it('does not match an unrelated path', () => {
    expect(isNavPathActive('/customers', '/tickets')).toBe(false);
  });
});

describe('isWorkspaceActive', () => {
  it('treats both the index route and the literal /workspace path as current', () => {
    expect(isWorkspaceActive('/')).toBe(true);
    expect(isWorkspaceActive('/workspace')).toBe(true);
  });

  it('is false for any other route', () => {
    expect(isWorkspaceActive('/tickets')).toBe(false);
  });
});

describe('isNavItemActive', () => {
  it('applies the workspace alias rule only for the "/" item', () => {
    expect(isNavItemActive('/workspace', { path: '/' })).toBe(true);
    expect(isNavItemActive('/workspace', { path: '/tickets' })).toBe(false);
  });

  it('matches a nested admin detail route to its list item', () => {
    expect(isNavItemActive('/admin/branches/9f1', { path: '/admin/branches' })).toBe(true);
  });
});

describe('hasActiveDescendant', () => {
  it('is true when the current route matches one child', () => {
    expect(
      hasActiveDescendant('/admin/branches/9f1', [{ path: '/admin/departments' }, { path: '/admin/branches' }])
    ).toBe(true);
  });

  it('is false when no child matches', () => {
    expect(hasActiveDescendant('/tickets', [{ path: '/admin/departments' }, { path: '/admin/branches' }])).toBe(false);
  });
});
