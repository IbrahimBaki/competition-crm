import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { Team } from '../types';
import { DeactivateEntityAction } from './DeactivateEntityAction';

interface TeamListTableProps {
  rows: Team[];
}

export function TeamListTable({ rows }: TeamListTableProps) {
  const { t } = useTranslation();

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200 text-sm">
        <thead className="bg-gray-50">
          <tr>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.organisation.team.column.name')}
            </th>
            <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
              {t('admin.organisation.team.column.code')}
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
          {rows.map((team) => (
            <tr key={team.id} className="hover:bg-gray-50">
              <td className="px-3 py-2 font-medium text-gray-900">
                <Link to={`/admin/teams/${team.id}`} className="hover:underline">
                  {team.name}
                </Link>
              </td>
              <td className="px-3 py-2 text-gray-600">{team.code}</td>
              <td className="px-3 py-2">
                {team.isActive ? (
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
                <DeactivateEntityAction entityType="team" id={team.id} isActive={team.isActive} />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
