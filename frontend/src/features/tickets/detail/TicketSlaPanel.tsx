import { useTranslation } from 'react-i18next';
import type { TicketSlaBlock, TicketSlaPosition, SlaClockState } from '../types';

interface TicketSlaPanelProps {
  sla: TicketSlaBlock | null;
}

const STATE_BADGE_CLASSES: Record<SlaClockState, string> = {
  running: 'bg-blue-100 text-blue-800',
  paused: 'bg-gray-200 text-gray-700',
  met: 'bg-green-100 text-green-800',
  breached: 'bg-red-100 text-red-800',
  cancelled: 'bg-gray-100 text-gray-500',
};

// Display-only: every value here (state, target/elapsed/remaining minutes,
// due_at) comes verbatim from the server. Formatting is allowed; computing a
// remaining time, percentage or breach state from a timestamp client-side is
// forbidden (see app/Domains/Sla/Services/SlaClockService.php — that logic
// must never be mirrored in the browser). See the guard test
// no-sla-recalculation.test.ts.
export function TicketSlaPanel({ sla }: TicketSlaPanelProps) {
  const { t } = useTranslation();

  if (!sla || (!sla.first_response && !sla.resolution)) {
    return <p className="text-sm text-gray-400">{t('tickets.sla.none')}</p>;
  }

  return (
    <div className="flex flex-col gap-3">
      {sla.first_response && <SlaRow labelKey="tickets.sla.first_response" position={sla.first_response} />}
      {sla.resolution && <SlaRow labelKey="tickets.sla.resolution" position={sla.resolution} />}
    </div>
  );
}

function SlaRow({ labelKey, position }: { labelKey: string; position: TicketSlaPosition }) {
  const { t, i18n } = useTranslation();
  const dueAt = position.due_at
    ? new Intl.DateTimeFormat(i18n.language, { dateStyle: 'medium', timeStyle: 'short' }).format(
        new Date(position.due_at)
      )
    : null;

  return (
    <div className="rounded border border-gray-200 p-2 text-sm">
      <div className="mb-1 flex items-center justify-between">
        <span className="font-medium text-gray-700">{t(labelKey)}</span>
        <span className={`rounded px-2 py-0.5 text-xs font-medium ${STATE_BADGE_CLASSES[position.state]}`}>
          {t(`tickets.sla.state.${position.state}`)}
        </span>
      </div>
      <dl className="grid grid-cols-2 gap-x-2 gap-y-0.5 text-xs text-gray-600">
        {dueAt && (
          <>
            <dt>{t('tickets.sla.due_at')}</dt>
            <dd>{dueAt}</dd>
          </>
        )}
        <dt>{t('tickets.sla.target')}</dt>
        <dd>{t('tickets.sla.minutes', { count: position.target_minutes })}</dd>
        <dt>{t('tickets.sla.elapsed')}</dt>
        <dd>{t('tickets.sla.minutes', { count: position.elapsed_minutes })}</dd>
        <dt>{t('tickets.sla.remaining')}</dt>
        <dd>{t('tickets.sla.minutes', { count: position.remaining_minutes })}</dd>
      </dl>
      {position.warning_fired && (
        <p className="mt-1 text-xs font-medium text-amber-700">{t('tickets.sla.warning_fired')}</p>
      )}
    </div>
  );
}
