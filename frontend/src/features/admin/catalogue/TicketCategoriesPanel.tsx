import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetTicketCategories } from '@/api/generated/ticketing/ticketing';
import type { ApiPage } from '@/api/http/envelope';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { toTicketCategory } from '../api/wire';

export function TicketCategoriesPanel() {
  const { t } = useTranslation();

  const query = useGetTicketCategories({
    per_page: 100,
  }) as unknown as UseQueryResult<ApiPage<Record<string, unknown>>, unknown>;

  return (
    <AsyncBoundary
      query={query}
      isEmpty={(data) => !data.items.length}
      empty={<EmptyState title={t('admin.catalogue.categories.empty')} />}
    >
      {(page) => (
        <div className="space-y-4">
          <div className="overflow-x-auto rounded border border-gray-200">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
                    {t('admin.catalogue.categories.column.name')}
                  </th>
                  <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
                    {t('admin.catalogue.categories.column.depth')}
                  </th>
                  <th scope="col" className="px-3 py-2 text-start font-medium text-gray-600">
                    {t('admin.catalogue.categories.column.active')}
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {page.items.map((item) => {
                  const category = toTicketCategory(item);
                  return (
                    <tr key={category.id} className="hover:bg-gray-50">
                      <td className="px-3 py-2 font-medium text-gray-900" style={{ paddingLeft: `${(category.depth ?? 0) * 1.5 + 0.75}rem` }}>
                        {category.name.ar || category.name.en}
                      </td>
                      <td className="px-3 py-2 text-gray-600">{category.depth ?? 0}</td>
                      <td className="px-3 py-2 text-gray-600">
                        {t(category.isActive ? 'admin.common.yes' : 'admin.common.no')}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
          <p className="text-xs text-gray-500">{t('admin.catalogue.categories.read_only_note')}</p>
        </div>
      )}
    </AsyncBoundary>
  );
}
