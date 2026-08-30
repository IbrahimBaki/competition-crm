import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { useBranchListQuery } from '@/features/admin/organisation/useBranchListQuery';
import { BranchListTable } from '@/features/admin/organisation/BranchListTable';

export function BranchesPage() {
  const { t } = useTranslation();
  const { setState, query } = useBranchListQuery();

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-3xl font-bold text-gray-900">{t('admin.organisation.branch.title')}</h1>
        <ActionGuard permission={PERMISSIONS.ORG_BRANCHES_MANAGE_ANY}>
          <Link
            to="/admin/branches/new"
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
          empty={<EmptyState title={t('admin.organisation.branch.empty_title')} />}
        >
          {(page) => (
            <>
              <BranchListTable rows={page.items} />
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
