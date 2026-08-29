import { useTranslation } from 'react-i18next';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { RequirePermission } from '@/auth/RequirePermission';
import { PERMISSIONS } from '@/auth/permissions';
import { useCustomerListQuery } from '@/features/customers/list/useCustomerListQuery';
import { CustomerSearchBar } from '@/features/customers/list/CustomerSearchBar';
import { CustomerListTable } from '@/features/customers/list/CustomerListTable';
import { Link } from 'react-router-dom';
import { PageHeader } from '@/components/ui';

export function CustomersPage() {
  const { t } = useTranslation();
  const { state, searchDraft, setSearch, setStatus, setSort, setState, resetFilters, query } =
    useCustomerListQuery();

  const hasActiveFilters = Boolean(state.search) || Boolean(state.status);

  return (
    <div>
      <PageHeader title={t('pages.customers.title')} actions={<RequirePermission permission={PERMISSIONS.CUSTOMERS_CREATE}><Link className="ui-button ui-button--primary" to="/customers/new">{t('pages.customers.create_customer')}</Link></RequirePermission>} />

      <RequirePermission permission={PERMISSIONS.CUSTOMERS_VIEW}>
        <div className="mb-4">
          <CustomerSearchBar
            search={searchDraft}
            status={state.status}
            onSearchChange={setSearch}
            onStatusChange={setStatus}
            onClear={resetFilters}
          />
        </div>

        <div className="rounded border border-gray-200 bg-white">
          <AsyncBoundary
            query={query}
            isEmpty={(page) => page.items.length === 0}
            empty={
              <EmptyState
                title={
                  hasActiveFilters
                    ? t('customers.list.empty_search_title')
                    : t('customers.list.empty_title')
                }
                description={
                  hasActiveFilters ? t('customers.list.empty_search_description') : undefined
                }
                action={
                  hasActiveFilters ? (
                    <button type="button" onClick={resetFilters} className="text-blue-600 hover:underline">
                      {t('customers.filters.clear')}
                    </button>
                  ) : undefined
                }
              />
            }
          >
            {(page) => (
              <>
                <CustomerListTable rows={page.items} sort={state.sort} onSortChange={(sort) => setSort(sort)} />
                <div className="flex items-center justify-between border-t border-gray-100 px-3 py-2 text-sm text-gray-600">
                  <span>
                    {t('customers.list.pagination_summary', {
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
                      {t('customers.list.previous_page')}
                    </button>
                    <button
                      type="button"
                      disabled={page.meta.page >= page.meta.total_pages}
                      onClick={() => setState({ page: page.meta.page + 1 })}
                      className="rounded border border-gray-300 px-2 py-1 disabled:opacity-40"
                    >
                      {t('customers.list.next_page')}
                    </button>
                  </div>
                </div>
              </>
            )}
          </AsyncBoundary>
        </div>
      </RequirePermission>
    </div>
  );
}
