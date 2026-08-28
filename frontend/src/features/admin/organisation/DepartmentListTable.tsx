import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { Department } from '../types';
import { DeactivateEntityAction } from './DeactivateEntityAction';

interface DepartmentListTableProps {
  rows: Department[];
}

export function DepartmentListTable({ rows }: DepartmentListTableProps) {
  const { t } = useTranslation();

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200 text-sm">
        <thead className="bg-gray-50">
          <tr>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.organisation.department.column.name')}
            </th>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.organisation.department.column.code')}
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
          {rows.map((department) => (
            <tr key={department.id} className="hover:bg-gray-50">
              <td className="px-3 py-2 font-medium text-gray-900">
                <Link to={`/admin/departments/${department.id}`} className="hover:underline">
                  {department.name}
                </Link>
              </td>
              <td className="px-3 py-2 text-gray-600">{department.code}</td>
              <td className="px-3 py-2">
                {department.isActive ? (
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
                <DeactivateEntityAction entityType="department" id={department.id} isActive={department.isActive} />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
