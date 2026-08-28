import { useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetRole } from '@/api/generated/security/security';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { RoleEditor } from '@/features/admin/security/RoleEditor';
import { toRole } from '@/features/admin/api/wire';

export function RoleDetailPage() {
  const { t } = useTranslation();
  const { roleId } = useParams<{ roleId: string }>();
  const isNew = roleId === 'new';
  const id = roleId ?? '';

  const query = useGetRole(id, {
    query: { enabled: !isNew },
  }) as unknown as UseQueryResult<Record<string, unknown>, unknown>;

  if (isNew) {
    return (
      <div>
        <h1 className="mb-6 text-3xl font-bold text-gray-900">{t('admin.security.role.create_title')}</h1>
        <RoleEditor />
      </div>
    );
  }

  return (
    <AsyncBoundary query={query}>
      {(raw) => {
        const role = toRole(raw);
        return (
          <div>
            <h1 className="mb-6 text-3xl font-bold text-gray-900">
              {role.displayName.ar || role.displayName.en || role.name}
            </h1>
            <RoleEditor role={role} />
          </div>
        );
      }}
    </AsyncBoundary>
  );
}
