import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { useRoleListQuery } from '@/features/admin/security/useRoleListQuery';

export function RolesPage() {
  const { t } = useTranslation();
  const { setState, query } = useRoleListQuery();

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-3xl font-bold text-gray-900">{t('admin.security.role.title')}</h1>
        <ActionGuard permission={PERMISSIONS.ADMIN_ROLES_MANAGE}>
          <Link
            to="/admin/roles/new"
            className="rounded bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
          >
            {t('admin.organisation.actions.create')}
          </Link>
        </ActionGuard>
      </div>

      <div className="rounded border border-gray-200 bg-white">
        <AsyncBoundary
          query={query}
          isEmpty={(page) => page.items.length === 0}
          empty={<EmptyState title={t('admin.security.role.empty_title')} />}
        >
          {(page) => (
            <>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                  <thead className="bg-gray-50">
                    <tr>
                      <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
                        {t('admin.security.role.column.name')}
                      </th>
                      <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
                        {t('admin.security.role.column.permission_count')}
                      </th>
                      <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
                        {t('admin.security.role.column.system')}
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {page.items.map((role) => (
                      <tr key={role.id} className="hover:bg-gray-50">
                        <td className="px-3 py-2 font-medium text-gray-900">
                          <Link to={`/admin/roles/${role.id}`} className="hover:underline">
                            {role.displayName.ar || role.displayName.en || role.name}
                          </Link>
                        </td>
                        <td className="px-3 py-2 text-gray-600">{role.permissionKeys.length}</td>
                        <td className="px-3 py-2 text-gray-600">
                          {t(role.isSystem ? 'admin.common.yes' : 'admin.common.no')}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              <div className="flex items-center justify-between border-t border-gray-100 px-3 py-2 text-sm text-gray-600">
                <span>
                  {t('admin.organisation.pagination_summary', {
                    page: page.meta.page,
                    totalPages: page.meta.total_pages,
                    total: page.meta.total,
                  })}
                </span>
                <div className="flex gap-2">
                  <button
                    type="button"
                    disabled={page.meta.page <= 1}
                    onClick={() => setState({ page: page.meta.page - 1 })}
                    className="rounded border border-gray-300 px-2 py-1 disabled:opacity-40"
                  >
                    {t('admin.organisation.previous_page')}
                  </button>
                  <button
                    type="button"
                    disabled={page.meta.page >= page.meta.total_pages}
                    onClick={() => setState({ page: page.meta.page + 1 })}
                    className="rounded border border-gray-300 px-2 py-1 disabled:opacity-40"
                  >
                    {t('admin.organisation.next_page')}
                  </button>
                </div>
              </div>
            </>
          )}
        </AsyncBoundary>
      </div>
    </div>
  );
}
