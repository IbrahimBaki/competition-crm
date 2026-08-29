import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import type { ApiPage } from '@/api/http/envelope';
import { PortalAsyncBoundary as AsyncBoundary, PortalEmptyState as EmptyState } from '@/portal/components/PortalStates';
import { Badge, Button, Card, PageHeader, Pagination } from '@/components/ui';

interface PortalTicket { uuid: string; reference: string; subject: string; status?: string; priority?: string; created_at?: string; updated_at?: string }

export function PortalTicketsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [page, setPage] = useState(1);
  const query = useQuery({ queryKey: ['portal', 'tickets', page], queryFn: () => apiRequest<ApiPage<PortalTicket>>({ url: '/portal/tickets', method: 'GET', params: { page, per_page: 20 } }) });

  return (
    <div className="max-w-5xl mx-auto px-4 py-8">
      <PageHeader title={t('portal.tickets.title')} description="Track requests, responses, and resolution progress." actions={<Button onClick={() => navigate('/portal/tickets/new')}>{t('portal.tickets.new_button')}</Button>} />
      <AsyncBoundary query={query} isEmpty={(data) => data.items.length === 0} empty={<EmptyState title={t('portal.tickets.empty')} action={<Button onClick={() => navigate('/portal/tickets/new')}>{t('portal.tickets.new_button')}</Button>} />}>
        {(data) => <Card className="overflow-hidden"><div className="divide-y divide-slate-100">{data.items.map((ticket) => <button key={ticket.uuid} onClick={() => navigate(`/portal/tickets/${ticket.uuid}`)} className="w-full p-4 text-start transition hover:bg-slate-50"><div className="flex flex-wrap items-center justify-between gap-3"><div><p className="m-0 text-xs font-semibold text-blue-700">{ticket.reference}</p><h2 className="mt-1 text-base font-semibold text-slate-900">{ticket.subject}</h2><p className="mt-1 text-sm text-slate-500">Updated {ticket.updated_at ? new Date(ticket.updated_at).toLocaleString() : 'recently'}</p></div><Badge tone="info">{ticket.status ?? 'Open'}</Badge></div></button>)}</div><Pagination page={data.meta.page} totalPages={data.meta.total_pages} total={data.meta.total} onPageChange={setPage} label="requests" /></Card>}
      </AsyncBoundary>
    </div>
  );
}
