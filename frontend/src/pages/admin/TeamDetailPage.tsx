import { useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import type { UseQueryResult } from '@tanstack/react-query';
import { useGetTeam } from '@/api/generated/organization/organization';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import { TeamForm } from '@/features/admin/organisation/TeamForm';
import { RouteFormModal } from '@/shell/RouteFormModal';
import { toTeam } from '@/features/admin/api/wire';

export function TeamDetailPage() {
  const { t } = useTranslation();
  const { teamId } = useParams<{ teamId: string }>();
  const isNew = teamId === 'new';
  const id = teamId ?? '';

  const query = useGetTeam(id, {
    query: { enabled: !isNew },
  }) as unknown as UseQueryResult<Record<string, unknown>, unknown>;

  if (isNew) {
    return <RouteFormModal title={t('admin.organisation.team.create_title')} fallback="/admin/teams"><TeamForm /></RouteFormModal>;
  }

  return (
    <AsyncBoundary query={query}>
      {(raw) => {
        const team = toTeam(raw);
        return (
          <div>
            <h1 className="mb-6 text-3xl font-bold text-gray-900">{team.name}</h1>
            <ActionGuard permission={PERMISSIONS.ORG_TEAMS_MANAGE_ANY}>
              <TeamForm team={team} />
            </ActionGuard>
          </div>
        );
      }}
    </AsyncBoundary>
  );
}
