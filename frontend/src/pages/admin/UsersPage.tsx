import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { EmptyState } from '@/shell/states/EmptyState';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { useUserListQuery } from '@/features/admin/security/useUserListQuery';
import { UserListTable } from '@/features/admin/security/UserListTable';
import { InviteUserDialog } from '@/features/admin/security/InviteUserDialog';
import { UserPlacementPanel } from '@/features/admin/security/UserPlacementPanel';
import type { AdminUser } from '@/features/admin/types';
import { Button, Dialog, useToast } from '@/components/ui';
import { apiRequest } from '@/api/http/mutator';

export function UsersPage() {
  const { t } = useTranslation();
  const { setState, query } = useUserListQuery();
  const [inviteOpen, setInviteOpen] = useState(false);
  const [placementUser, setPlacementUser] = useState<AdminUser | null>(null);
  const [eraseUser,setEraseUser]=useState<AdminUser|null>(null); const [erasing,setErasing]=useState(false); const { notify }=useToast();
  const erase=async()=>{if(!eraseUser)return;setErasing(true);try{await apiRequest({url:`/users/${eraseUser.id}/erase-personal-data`,method:'POST',headers:{'Idempotency-Key':crypto.randomUUID()}});await query.refetch();notify('Personal data erased.');setEraseUser(null);}catch{notify('Personal data could not be erased.','danger');}finally{setErasing(false);}};

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-3xl font-bold text-gray-900">{t('admin.security.user.title')}</h1>
        <ActionGuard permission={PERMISSIONS.ADMIN_USERS_INVITE}>
          <button
            type="button"
            onClick={() => setInviteOpen(true)}
            className="rounded bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
          >
            {t('admin.security.user.invite_button')}
          </button>
        </ActionGuard>
      </div>

      <div className="rounded border border-gray-200 bg-white">
        <AsyncBoundary
          query={query}
          isEmpty={(page) => page.items.length === 0}
          empty={<EmptyState title={t('admin.security.user.empty_title')} />}
        >
          {(page) => (
            <>
              <UserListTable rows={page.items} onManagePlacement={setPlacementUser} onErase={setEraseUser} />
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

      {inviteOpen && (
        <InviteUserDialog onClose={() => setInviteOpen(false)} onInvited={() => setInviteOpen(false)} />
      )}

      {placementUser && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="w-full max-w-lg space-y-3 rounded bg-white p-5 shadow-lg">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-900">
                {t('admin.security.user.placement.heading', { name: placementUser.name || placementUser.email })}
              </h2>
              <button
                type="button"
                onClick={() => setPlacementUser(null)}
                className="text-sm text-gray-500 hover:text-gray-700"
              >
                {t('admin.organisation.actions.cancel')}
              </button>
            </div>
            <UserPlacementPanel userId={placementUser.id} />
          </div>
        </div>
      )}
      <Dialog open={Boolean(eraseUser)} title="Erase personal data?" description="This irreversible action anonymises the selected staff identity and removes security credentials." onClose={()=>setEraseUser(null)} footer={<><Button variant="secondary" onClick={()=>setEraseUser(null)}>Cancel</Button><Button variant="danger" busy={erasing} onClick={()=>void erase()}>Erase personal data</Button></>}><p className="text-sm">Selected account: <strong>{eraseUser?.email}</strong></p></Dialog>
    </div>
  );
}
