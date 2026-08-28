import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ConfirmActionDialog } from '@/shared/confirm/ConfirmActionDialog';
import { normaliseApiError } from '@/api/http/errors';
import { adminErrorMessageKey } from '../api/errorCodes';
import { useSetBranchActive, useSetDepartmentActive, useSetTeamActive } from '../api/wire';

export type OrganisationEntityType = 'branch' | 'department' | 'team';

interface DeactivateEntityActionProps {
  entityType: OrganisationEntityType;
  id: string;
  isActive: boolean;
}

// Drives activate/deactivate for branches, departments and teams from one
// component. Deactivation is the only consequential direction — the backend
// guards it with a 409 when the entity still has active children (branch ->
// departments) or open tickets (department -> tickets); that guard is
// rendered inline on the dialog and keeps it open rather than surfacing a
// generic toast. Activation is reversible and non-destructive, so it applies
// immediately without a confirmation step.
export function DeactivateEntityAction({ entityType, id, isActive }: DeactivateEntityActionProps) {
  const { t } = useTranslation();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [error, setError] = useState<string | undefined>();

  const branchMutation = useSetBranchActive({
    onSuccess: () => setDialogOpen(false),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });
  const departmentMutation = useSetDepartmentActive({
    onSuccess: () => setDialogOpen(false),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });
  const teamMutation = useSetTeamActive({
    onSuccess: () => setDialogOpen(false),
    onError: (err) => setError(t(adminErrorMessageKey(normaliseApiError(err).code ?? undefined))),
  });

  const mutationByType = {
    branch: branchMutation,
    department: departmentMutation,
    team: teamMutation,
  } as const;
  const mutation = mutationByType[entityType];

  const activate = () => {
    if (entityType === 'branch') branchMutation.mutate({ branch: id, active: true });
    if (entityType === 'department') departmentMutation.mutate({ department: id, active: true });
    if (entityType === 'team') teamMutation.mutate({ team: id, active: true });
  };

  const confirmDeactivate = () => {
    if (entityType === 'branch') branchMutation.mutate({ branch: id, active: false });
    if (entityType === 'department') departmentMutation.mutate({ department: id, active: false });
    if (entityType === 'team') teamMutation.mutate({ team: id, active: false });
  };

  const openDialog = () => {
    setError(undefined);
    setDialogOpen(true);
  };

  const closeDialog = () => {
    setDialogOpen(false);
    setError(undefined);
  };

  if (isActive) {
    return (
      <>
        <button
          type="button"
          onClick={openDialog}
          className="rounded border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50"
        >
          {t('admin.organisation.actions.deactivate')}
        </button>

        {dialogOpen && (
          <ConfirmActionDialog
            titleKey={`admin.organisation.${entityType}.deactivate_dialog_title`}
            consequenceKey={`admin.organisation.${entityType}.deactivate_consequence`}
            confirmLabelKey="admin.organisation.actions.deactivate"
            onConfirm={confirmDeactivate}
            onCancel={closeDialog}
            isSubmitting={mutation.isPending}
            error={error}
            destructive
          />
        )}
      </>
    );
  }

  return (
    <button
      type="button"
      onClick={activate}
      disabled={mutation.isPending}
      className="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
    >
      {t('admin.organisation.actions.activate')}
    </button>
  );
}
