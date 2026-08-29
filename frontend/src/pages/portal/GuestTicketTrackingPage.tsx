import { useTranslation } from 'react-i18next';
import { useParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import { PortalAsyncBoundary as AsyncBoundary } from '@/portal/components/PortalStates';
import { Badge, Card, PageHeader } from '@/components/ui';

interface GuestTicket { uuid?: string; reference?: string; subject?: string; status?: string; updated_at?: string }
export function GuestTicketTrackingPage() {
  const { t } = useTranslation();
  const { token = '' } = useParams();
  const query = useQuery({ queryKey: ['portal', 'guest', token], queryFn: () => apiRequest<GuestTicket>({ url: `/portal/guest/tickets/${token}`, method: 'GET' }), enabled: Boolean(token) });
  return <div className="max-w-3xl mx-auto px-4 py-8"><PageHeader title={t('portal.guest_tracking.title')} description="This private link shows the current status of your support request."/><AsyncBoundary query={query}>{(ticket) => <Card className="p-6"><div className="flex flex-wrap items-center justify-between gap-4"><div><p className="text-sm font-semibold text-blue-700">{ticket.reference}</p><h2 className="mt-1 text-xl font-semibold">{ticket.subject}</h2></div><Badge tone="info">{ticket.status ?? 'Open'}</Badge></div>{ticket.updated_at && <p className="mt-5 text-sm text-slate-500">Last updated {new Date(ticket.updated_at).toLocaleString()}</p>}</Card>}</AsyncBoundary></div>;
}
