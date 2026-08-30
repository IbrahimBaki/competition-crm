import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { pickBilingual } from '../../tickets/utils/bilingual';
import { useQuickRepliesQuery } from './useQuickRepliesQuery';

interface QuickReplyPickerProps {
  onSelect: (replyId: string) => void;
  onClose: () => void;
}

export function QuickReplyPicker({ onSelect, onClose }: QuickReplyPickerProps) {
  const { t, i18n } = useTranslation();
  const [search, setSearch] = useState('');
  const query = useQuickRepliesQuery(50);

  const items = (query.data?.items ?? []).filter((reply) => {
    if (!search.trim()) return true;
    const label = pickBilingual(reply.title, i18n.language).toLowerCase();
    return label.includes(search.trim().toLowerCase());
  });

  return (
    <div className="absolute z-10 mt-1 w-72 rounded border border-gray-200 bg-white shadow-lg">
      <div className="border-b border-gray-100 p-2">
        <input
          value={search}
          onChange={(event) => setSearch(event.target.value)}
          placeholder={t('workspace.quick_replies.search_placeholder')}
          className="w-full rounded border border-gray-300 p-1.5 text-xs"
          autoFocus
        />
      </div>
      <ul className="max-h-56 overflow-y-auto">
        {query.isLoading && <li className="p-2 text-xs text-gray-400">{t('workspace.quick_replies.loading')}</li>}
        {!query.isLoading && items.length === 0 && (
          <li className="p-2 text-xs text-gray-400">{t('workspace.quick_replies.empty')}</li>
        )}
        {items.map((reply) => (
          <li key={reply.uuid}>
            <button
              type="button"
              onClick={() => {
                onSelect(reply.uuid);
                onClose();
              }}
              className="block w-full truncate p-2 text-start text-sm hover:bg-gray-50"
            >
              {pickBilingual(reply.title, i18n.language)}
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
}
