import { useTranslation } from 'react-i18next';
import type { AdminUser } from '../types';
import { UserLifecycleActions } from './UserLifecycleActions';

interface UserListTableProps {
  rows: AdminUser[];
  onManagePlacement: (user: AdminUser) => void;
}

// Columns are deliberately limited to identity + status: UserResource
// carries no roles/branch/department fields and there is no "get one user"
// endpoint (see .squad/gaps/36-483.md #5), so there is no detail route to
// link to either — lifecycle and placement actions are driven from this row
// directly, using only the fields the list response already returned.
export function UserListTable({ rows, onManagePlacement }: UserListTableProps) {
  const { t } = useTranslation();

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200 text-sm">
        <thead className="bg-gray-50">
          <tr>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.security.user.column.name')}
            </th>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.security.user.column.email')}
            </th>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.organisation.column.status')}
            </th>
            <th scope="col" className="px-3 py-2 text-end font-medium text-gray-600">
              {t('admin.organisation.column.actions')}
            </th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {rows.map((user) => (
            <tr key={user.id} className="hover:bg-gray-50">
              <td className="px-3 py-2 font-medium text-gray-900">{user.name || user.email}</td>
              <td className="px-3 py-2 text-gray-600">{user.email}</td>
              <td className="px-3 py-2">
                {user.status === 'active' ? (
                  <span className="rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                    {t('admin.organisation.status.active')}
                  </span>
                ) : (
                  <span className="rounded bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">
                    {t('admin.organisation.status.inactive')}
                  </span>
                )}
              </td>
              <td className="px-3 py-2 text-end">
                <div className="flex justify-end gap-2">
                  <button
                    type="button"
                    onClick={() => onManagePlacement(user)}
                    className="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                  >
                    {t('admin.security.user.manage_placement')}
                  </button>
                  <UserLifecycleActions user={user} />
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
