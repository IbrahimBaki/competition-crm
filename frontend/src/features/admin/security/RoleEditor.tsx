import { useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetPermissionsCatalogue, useGetUsers } from '@/api/generated/security/security';
import type { ApiPage } from '@/api/http/envelope';
import { normaliseApiError } from '@/api/http/errors';
import { adminErrorMessageKey } from '../api/errorCodes';
import {
  toAdminUser,
  toPermissionCatalogue,
  useCreateRole,
  useUpdateRole,
  useDeleteRole,
  useAttachRoleToUser,
  useDetachRoleFromUser,
} from '../api/wire';
import type { Role } from '../types';

interface RoleEditorProps {
  role?: Role;
}

// Permission keys are grouped by the catalogue's own `module` field — there
// is no description field on the backend (see .squad/gaps/36-483.md #2), so
// each key renders as its raw string under a translated module heading.
// TODO(FE-06): permission descriptions missing from catalogue endpoint.
//
// There is no separate "immutable" flag: `RolePolicy::delete` blocks any
// `is_system` role, but `update` does not — all roles remain editable here.
// The administrator role additionally rejects removing any `admin.*` key on
// save (422 `admin_role_locked`), handled inline like any other guard-rail
// code rather than pre-emptively locking the form (see
// .squad/gaps/36-483.md #6 for why the plan's "read-only for
// system/administrator roles" instruction doesn't match the real backend).
export function RoleEditor({ role }: RoleEditorProps) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const isEdit = Boolean(role);

  const [displayNameAr, setDisplayNameAr] = useState(role?.displayName.ar ?? '');
  const [displayNameEn, setDisplayNameEn] = useState(role?.displayName.en ?? '');
  const [name, setName] = useState(role?.name ?? '');
  const [permissionKeys, setPermissionKeys] = useState<string[]>(role?.permissionKeys ?? []);
  const [error, setError] = useState<string | undefined>();
  const [memberUserId, setMemberUserId] = useState('');
  const [memberMessage, setMemberMessage] = useState<string | undefined>();

  const catalogueQuery = useGetPermissionsCatalogue() as unknown as UseQueryResult<Record<string, unknown>, unknown>;
  const groups = catalogueQuery.data ? toPermissionCatalogue(catalogueQuery.data) : [];

  const usersQuery = useGetUsers({ per_page: 100 }) as unknown as UseQueryResult<
    ApiPage<Record<string, unknown>>,
    unknown
  >;
  const users = (usersQuery.data?.items ?? []).map(toAdminUser);

  const createMutation = useCreateRole({
    onSuccess: (created) => navigate(`/admin/roles/${created.id}`),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });
  const updateMutation = useUpdateRole({
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });
  const deleteMutation = useDeleteRole({
    onSuccess: () => navigate('/admin/roles'),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });
  const attachMutation = useAttachRoleToUser({
    onSuccess: () => setMemberMessage(t('admin.security.role.member_attached')),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });
  const detachMutation = useDetachRoleFromUser({
    onSuccess: () => setMemberMessage(t('admin.security.role.member_detached')),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const isPending = createMutation.isPending || updateMutation.isPending;
  const canSubmit =
    displayNameAr.trim().length > 0 && displayNameEn.trim().length > 0 && (isEdit || name.trim().length > 0);

  const togglePermission = (key: string) => {
    setPermissionKeys((current) => (current.includes(key) ? current.filter((k) => k !== key) : [...current, key]));
  };

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    setError(undefined);
    const displayName = { ar: displayNameAr.trim(), en: displayNameEn.trim() };

    if (isEdit && role) {
      updateMutation.mutate({ role: role.id, displayName, permissionKeys });
    } else {
      createMutation.mutate({ name: name.trim(), displayName, permissionKeys });
    }
  };

  return (
    <div className="space-y-6">
      <form onSubmit={handleSubmit} className="max-w-lg space-y-4">
        {!isEdit && (
          <div>
            <label htmlFor="role-name" className="mb-1 block text-xs font-medium text-gray-600">
              {t('admin.security.role.name_label')}
            </label>
            <input
              id="role-name"
              value={name}
              onChange={(event) => setName(event.target.value)}
              required
              className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
            />
          </div>
        )}

        <div className="grid grid-cols-2 gap-3">
          <div>
            <label htmlFor="role-display-name-ar" className="mb-1 block text-xs font-medium text-gray-600">
              {t('admin.organisation.name_ar_label')}
            </label>
            <input
              id="role-display-name-ar"
              value={displayNameAr}
              onChange={(event) => setDisplayNameAr(event.target.value)}
              dir="rtl"
              required
              className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
            />
          </div>
          <div>
            <label htmlFor="role-display-name-en" className="mb-1 block text-xs font-medium text-gray-600">
              {t('admin.organisation.name_en_label')}
            </label>
            <input
              id="role-display-name-en"
              value={displayNameEn}
              onChange={(event) => setDisplayNameEn(event.target.value)}
              dir="ltr"
              required
              className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
            />
          </div>
        </div>

        <div>
          <p className="mb-2 text-xs font-medium text-gray-600">{t('admin.security.role.permissions_heading')}</p>
          <div className="max-h-96 space-y-4 overflow-y-auto rounded border border-gray-200 p-3">
            {groups.map((group) => (
              <div key={group.module}>
                <p className="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{group.module}</p>
                <div className="space-y-1">
                  {group.entries.map((entry) => (
                    <label key={entry.key} className="flex items-center gap-2 text-sm text-gray-700">
                      <input
                        type="checkbox"
                        checked={permissionKeys.includes(entry.key)}
                        onChange={() => togglePermission(entry.key)}
                      />
                      {entry.key}
                    </label>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>

        {error && <p className="text-sm text-red-600">{error}</p>}

        <div className="flex items-center gap-2">
          <button
            type="submit"
            disabled={!canSubmit || isPending}
            className="rounded bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {t(isEdit ? 'admin.organisation.actions.save' : 'admin.organisation.actions.create')}
          </button>
          {isEdit && role && !role.isSystem && (
            <button
              type="button"
              onClick={() => deleteMutation.mutate({ role: role.id })}
              disabled={deleteMutation.isPending}
              className="rounded border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50 disabled:opacity-50"
            >
              {t('admin.organisation.actions.delete')}
            </button>
          )}
        </div>
      </form>

      {isEdit && role && (
        <div className="max-w-lg space-y-2 rounded border border-gray-200 bg-white p-4">
          <p className="text-sm font-medium text-gray-900">{t('admin.security.role.members_heading')}</p>
          <p className="text-xs text-gray-500">{t('admin.security.role.members_write_only_note')}</p>
          <div className="flex items-center gap-2">
            <select
              value={memberUserId}
              onChange={(event) => setMemberUserId(event.target.value)}
              className="flex-1 rounded border border-gray-300 px-2 py-1.5 text-sm"
            >
              <option value="" disabled>
                {t('admin.common.select_placeholder')}
              </option>
              {users.map((user) => (
                <option key={user.id} value={user.id}>
                  {user.name ? `${user.name} (${user.email})` : user.email}
                </option>
              ))}
            </select>
            <button
              type="button"
              disabled={!memberUserId || attachMutation.isPending}
              onClick={() => attachMutation.mutate({ role: role.id, user: memberUserId })}
              className="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              {t('admin.security.user.placement.attach')}
            </button>
            <button
              type="button"
              disabled={!memberUserId || detachMutation.isPending}
              onClick={() => detachMutation.mutate({ role: role.id, user: memberUserId })}
              className="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              {t('admin.security.user.placement.detach')}
            </button>
          </div>
          {memberMessage && <p className="text-sm text-green-700">{memberMessage}</p>}
        </div>
      )}
    </div>
  );
}
