import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ActionGuard } from '@/shell/ActionGuard';
import { PERMISSIONS } from '@/auth/permissions';
import type { TicketFilters } from './useTicketListQuery';

// The backend exposes TicketSavedView / TicketSavedViewResource but no route
// for it exists in routes/api.php (grep confirms). Saved views are therefore
// stored client-side only, scoped per browser. See story notes / PR
// description: this should move server-side once the endpoint ships.
const STORAGE_KEY = 'tickets.savedViews.v1';

export interface SavedTicketView {
  id: string;
  name: string;
  sort?: string;
  search?: string;
  filters: TicketFilters;
}

function readSavedViews(): SavedTicketView[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function writeSavedViews(views: SavedTicketView[]): void {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(views));
  } catch {
    // Storage unavailable (private browsing, quota) — saved views just won't persist.
  }
}

interface SavedViewsBarProps {
  currentSort?: string;
  currentSearch?: string;
  currentFilters: TicketFilters;
  onApply: (view: SavedTicketView) => void;
}

export function SavedViewsBar({ currentSort, currentSearch, currentFilters, onApply }: SavedViewsBarProps) {
  const { t } = useTranslation();
  const [views, setViews] = useState<SavedTicketView[]>(() => readSavedViews());
  const [isCreating, setIsCreating] = useState(false);
  const [nameDraft, setNameDraft] = useState('');

  const handleCreate = () => {
    const name = nameDraft.trim();
    if (!name) return;

    const view: SavedTicketView = {
      id: crypto.randomUUID(),
      name,
      sort: currentSort,
      search: currentSearch,
      filters: currentFilters,
    };

    const next = [...views, view];
    setViews(next);
    writeSavedViews(next);
    setNameDraft('');
    setIsCreating(false);
  };

  const handleDelete = (id: string) => {
    const next = views.filter((view) => view.id !== id);
    setViews(next);
    writeSavedViews(next);
  };

  return (
    <div className="flex flex-wrap items-center gap-2 text-sm">
      <span className="font-medium text-gray-600">{t('tickets.saved_views.label')}</span>
      {views.length === 0 && !isCreating && (
        <span className="text-gray-400">{t('tickets.saved_views.none')}</span>
      )}
      {views.map((view) => (
        <span
          key={view.id}
          className="inline-flex items-center gap-1 rounded-full border border-gray-300 bg-white px-3 py-1"
        >
          <button type="button" onClick={() => onApply(view)} className="hover:underline">
            {view.name}
          </button>
          <ActionGuard permission={PERMISSIONS.TICKETS_VIEW_ANY}>
            <button
              type="button"
              onClick={() => handleDelete(view.id)}
              aria-label={t('tickets.saved_views.delete', { name: view.name })}
              className="text-gray-400 hover:text-red-600"
            >
              ×
            </button>
          </ActionGuard>
        </span>
      ))}

      <ActionGuard permission={PERMISSIONS.TICKETS_VIEW_ANY}>
        {isCreating ? (
          <span className="inline-flex items-center gap-1">
            <input
              autoFocus
              type="text"
              value={nameDraft}
              onChange={(event) => setNameDraft(event.target.value)}
              onKeyDown={(event) => event.key === 'Enter' && handleCreate()}
              placeholder={t('tickets.saved_views.name_placeholder')}
              className="rounded border border-gray-300 px-2 py-1 text-sm"
            />
            <button
              type="button"
              onClick={handleCreate}
              className="rounded bg-blue-600 px-2 py-1 text-white hover:bg-blue-700"
            >
              {t('tickets.saved_views.save')}
            </button>
            <button type="button" onClick={() => setIsCreating(false)} className="text-gray-500">
              {t('tickets.saved_views.cancel')}
            </button>
          </span>
        ) : (
          <button type="button" onClick={() => setIsCreating(true)} className="text-blue-600 hover:underline">
            {t('tickets.saved_views.create')}
          </button>
        )}
      </ActionGuard>
    </div>
  );
}
