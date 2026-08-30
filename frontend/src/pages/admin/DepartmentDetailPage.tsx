import { useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetDepartment } from '@/api/generated/organization/organization';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { DepartmentForm } from '@/features/admin/organisation/DepartmentForm';
import { toDepartment } from '@/features/admin/api/wire';

export function DepartmentDetailPage() {
  const { t } = useTranslation();
  const { departmentId } = useParams<{ departmentId: string }>();
  const isNew = departmentId === 'new';
  const id = departmentId ?? '';

  const query = useGetDepartment(id, {
    query: { enabled: !isNew },
  }) as unknown as UseQueryResult<Record<string, unknown>, unknown>;

  if (isNew) {
    return (
      <div>
        <h1 className="mb-6 text-3xl font-bold text-gray-900">{t('admin.organisation.department.create_title')}</h1>
        <DepartmentForm />
      </div>
    );
  }

  return (
    <AsyncBoundary query={query}>
      {(raw) => {
        const department = toDepartment(raw);
        return (
          <div>
            <h1 className="mb-6 text-3xl font-bold text-gray-900">{department.name}</h1>
            <ActionGuard permission={PERMISSIONS.ORG_DEPARTMENTS_MANAGE_ANY}>
              <DepartmentForm department={department} />
            </ActionGuard>
          </div>
        );
      }}
    </AsyncBoundary>
  );
}
