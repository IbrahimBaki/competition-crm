import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { Branch } from '../types';
import { DeactivateEntityAction } from './DeactivateEntityAction';

interface BranchListTableProps {
  rows: Branch[];
}

// `name` renders in whichever locale the request was made in (see
// .squad/gaps/frontend/36-483.md #1) — there is no second-locale value to
// show as a title attribute on this endpoint, unlike the ticket/customer
// tables that receive a full bilingual payload.
export function BranchListTable({ rows }: BranchListTableProps) {
  const { t } = useTranslation();

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200 text-sm">
        <thead className="bg-gray-50">
          <tr>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.organisation.branch.column.name')}
            </th>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.organisation.branch.column.code')}
            </th>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.organisation.branch.column.timezone')}
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
          {rows.map((branch) => (
            <tr key={branch.id} className="hover:bg-gray-50">
              <td className="px-3 py-2 font-medium text-gray-900">
                <Link to={`/admin/branches/${branch.id}`} className="hover:underline">
                  {branch.name}
                </Link>
              </td>
              <td className="px-3 py-2 text-gray-600">{branch.code}</td>
              <td className="px-3 py-2 text-gray-600">{branch.timezone}</td>
              <td className="px-3 py-2">
                {branch.isActive ? (
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
                <DeactivateEntityAction entityType="branch" id={branch.id} isActive={branch.isActive} />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
