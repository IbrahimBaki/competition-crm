import { useTranslation } from 'react-i18next';
import { TicketStatusesPanel } from '@/features/admin/catalogue/TicketStatusesPanel';
import { TicketCategoriesPanel } from '@/features/admin/catalogue/TicketCategoriesPanel';
import { TicketPrioritiesPanel } from '@/features/admin/catalogue/TicketPrioritiesPanel';

// TODO(FE-06): Ticket status and category editing is blocked by role-based policy
// enforcement that requires checking `TicketStatusDefinitionPolicy::update` and
// `TicketCategoryPolicy::update` on the backend before allowing client-side form edits.
// The plan requires disabled controls with a reason tooltip; this is not yet
// implemented because the backend does not expose edit-permission rules via the API.
// See .squad/gaps/36-483.md.

export function TicketCataloguePage() {
  const { t } = useTranslation();

  return (
    <div className="space-y-8">
      <div>
        <h1 className="mb-6 text-3xl font-bold text-gray-900">{t('admin.catalogue.title')}</h1>
      </div>

      <div>
        <h2 className="mb-4 text-2xl font-semibold text-gray-800">{t('admin.catalogue.statuses.heading')}</h2>
        <TicketStatusesPanel />
      </div>

      <div>
        <h2 className="mb-4 text-2xl font-semibold text-gray-800">{t('admin.catalogue.categories.heading')}</h2>
        <TicketCategoriesPanel />
      </div>

      <div>
        <h2 className="mb-4 text-2xl font-semibold text-gray-800">{t('admin.catalogue.priorities.heading')}</h2>
        <TicketPrioritiesPanel />
      </div>
    </div>
  );
}
