import { useTranslation } from 'react-i18next';

// Priorities are a fixed system enum, not a CRUD resource.
// They are defined in app/Domains/Ticketing/Models/TicketPriority.php
// and cannot be modified via the API.
const PRIORITY_ENUM = ['urgent', 'high', 'normal', 'low'] as const;

export function TicketPrioritiesPanel() {
  const { t } = useTranslation();

  return (
    <div className="space-y-4">
      <div className="rounded border border-yellow-200 bg-yellow-50 p-4">
        <p className="text-sm text-yellow-800">{t('admin.catalogue.priorities.system_enum_note')}</p>
      </div>

      <div className="overflow-x-auto rounded border border-gray-200">
        <table className="min-w-full divide-y divide-gray-200 text-sm">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
                {t('admin.catalogue.priorities.column.name')}
              </th>
              <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
                {t('admin.catalogue.priorities.column.value')}
              </th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {PRIORITY_ENUM.map((priority) => (
              <tr key={priority} className="hover:bg-gray-50">
                <td className="px-3 py-2 font-medium text-gray-900">
                  {priority.charAt(0).toUpperCase() + priority.slice(1)}
                </td>
                <td className="px-3 py-2 text-gray-600">{priority}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
