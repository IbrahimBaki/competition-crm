import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useGetDepartments } from '@/api/generated/organization/organization';
import { pickBilingual, type BilingualValue } from '../utils/bilingual';
import type { ApiPage } from '@/api/http/envelope';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import {
  useAssignTicket,
  useUnassignTicket,
  useClaimTicket,
  useTransferTicketToAgent,
  useTransferTicketToDepartment,
} from '../api/wire';
import { useTicketMutation } from '../useTicketMutation';
import { ConflictBanner } from './ConflictBanner';
import type { TicketDetail } from '../types';

interface DepartmentOption {
  id: string;
  name: BilingualValue;
}

interface TicketAssignmentControlProps {
  ticket: TicketDetail;
}

export function TicketAssignmentControl({ ticket }: TicketAssignmentControlProps) {
  const { t, i18n } = useTranslation();
  const [assigneeUuid, setAssigneeUuid] = useState('');
  const [transferUuid, setTransferUuid] = useState('');
  const [departmentId, setDepartmentId] = useState('');
  const [keepAssignee, setKeepAssignee] = useState(false);

  const departmentsQuery = useGetDepartments({ per_page: 100 }) as unknown as { data?: ApiPage<DepartmentOption> };

  const claim = useTicketMutation(useClaimTicket, ticket.id);
  const assign = useTicketMutation(useAssignTicket, ticket.id, {
    onSuccess: () => setAssigneeUuid(''),
  });
  const unassign = useTicketMutation(useUnassignTicket, ticket.id);
  const transferAgent = useTicketMutation(useTransferTicketToAgent, ticket.id, {
    onSuccess: () => setTransferUuid(''),
  });
  const transferDepartment = useTicketMutation(useTransferTicketToDepartment, ticket.id, {
    onSuccess: () => setDepartmentId(''),
  });

  const conflict =
    claim.conflict ?? assign.conflict ?? unassign.conflict ?? transferAgent.conflict ?? transferDepartment.conflict;
  const reloadLatest =
    claim.reloadLatest || assign.reloadLatest || unassign.reloadLatest || transferAgent.reloadLatest || transferDepartment.reloadLatest;

  const anyPending =
    claim.isPending || assign.isPending || unassign.isPending || transferAgent.isPending || transferDepartment.isPending;

  return (
    <div className="flex flex-col gap-3">
      {conflict && <ConflictBanner error={conflict} onReload={reloadLatest} />}

      <ActionGuard permission={PERMISSIONS.TICKETS_CLAIM}>
        <button
          type="button"
          disabled={anyPending}
          onClick={() => claim.mutate({ ticket: ticket.id, version: ticket.version })}
          className="w-full rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50 disabled:opacity-50"
        >
          {t('tickets.assignment.claim')}
        </button>
      </ActionGuard>

      <ActionGuard permission={PERMISSIONS.TICKETS_ASSIGN}>
        <div className="flex gap-2">
          <input
            type="text"
            value={assigneeUuid}
            onChange={(event) => setAssigneeUuid(event.target.value)}
            placeholder={t('tickets.filters.uuid_placeholder')}
            className="flex-1 rounded border border-gray-300 px-2 py-1 text-sm"
            disabled={anyPending}
          />
          <button
            type="button"
            disabled={!assigneeUuid.trim() || anyPending}
            onClick={() => assign.mutate({ ticket: ticket.id, assigneeUuid: assigneeUuid.trim(), version: ticket.version })}
            className="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {t('tickets.assignment.assign')}
          </button>
        </div>
        {ticket.assignee_id && (
          <button
            type="button"
            disabled={anyPending}
            onClick={() => unassign.mutate({ ticket: ticket.id })}
            className="text-sm text-gray-600 hover:underline disabled:opacity-50"
          >
            {t('tickets.assignment.unassign')}
          </button>
        )}
      </ActionGuard>

      <ActionGuard permission={PERMISSIONS.TICKETS_TRANSFER_AGENT}>
        <div className="flex gap-2">
          <input
            type="text"
            value={transferUuid}
            onChange={(event) => setTransferUuid(event.target.value)}
            placeholder={t('tickets.filters.uuid_placeholder')}
            className="flex-1 rounded border border-gray-300 px-2 py-1 text-sm"
            disabled={anyPending}
          />
          <button
            type="button"
            disabled={!transferUuid.trim() || anyPending}
            onClick={() =>
              transferAgent.mutate({ ticket: ticket.id, userUuid: transferUuid.trim(), version: ticket.version })
            }
            className="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50 disabled:opacity-50"
          >
            {t('tickets.assignment.transfer_to_agent')}
          </button>
        </div>
      </ActionGuard>

      <ActionGuard permission={PERMISSIONS.TICKETS_TRANSFER_DEPARTMENT}>
        <div className="flex flex-col gap-1">
          <div className="flex gap-2">
            <select
              value={departmentId}
              onChange={(event) => setDepartmentId(event.target.value)}
              className="flex-1 rounded border border-gray-300 px-2 py-1 text-sm"
              disabled={anyPending}
            >
              <option value="">{t('tickets.assignment.choose_department')}</option>
              {(departmentsQuery.data?.items ?? []).map((department) => (
                <option key={department.id} value={department.id}>
                  {pickBilingual(department.name, i18n.language)}
                </option>
              ))}
            </select>
            <button
              type="button"
              disabled={!departmentId || anyPending}
              onClick={() =>
                transferDepartment.mutate({
                  ticket: ticket.id,
                  departmentId,
                  keepAssignee,
                  version: ticket.version,
                })
              }
              className="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50 disabled:opacity-50"
            >
              {t('tickets.assignment.transfer_to_department')}
            </button>
          </div>
          <label className="flex items-center gap-1 text-xs text-gray-600">
            <input
              type="checkbox"
              checked={keepAssignee}
              onChange={(event) => setKeepAssignee(event.target.checked)}
            />
            {t('tickets.assignment.keep_assignee')}
          </label>
        </div>
      </ActionGuard>
    </div>
  );
}
