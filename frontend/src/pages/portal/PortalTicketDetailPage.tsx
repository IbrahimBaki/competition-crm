import { useTranslation } from 'react-i18next';
import { useParams } from 'react-router-dom';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import React, { useState } from 'react';
import { apiRequest } from '@/api/http/mutator';
import { PortalAsyncBoundary as AsyncBoundary } from '@/portal/components/PortalStates';
import { Badge, Button, Card, Field, FormErrorSummary, PageHeader, Select, Textarea, useToast } from '@/components/ui';
import { httpClient } from '@/api/http/client';
import { getPortalSession } from '@/portal/auth/portalSession';
import { normaliseApiError } from '@/api/http/errors';
import type { ApiPage } from '@/api/http/envelope';

interface Ticket { uuid: string; reference: string; subject: string; status?: string; priority?: string; created_at?: string }
interface Attachment { uuid: string; original_name: string; size?: number; download_url: string }
interface Message { uuid: string; body: string; author_type: string; created_at: string; attachments?: Attachment[] }

export function PortalTicketDetailPage() {
  const { t } = useTranslation();
  const { id = '' } = useParams();
  const queryClient = useQueryClient();
  const { notify } = useToast();
  const [body, setBody] = useState('');
  const [busy, setBusy] = useState(false);
  const [score, setScore] = useState('5');
  const [comment, setComment] = useState('');
  const [feedbackSent, setFeedbackSent] = useState(false);
  const [feedbackErrors, setFeedbackErrors] = useState<Record<string, string[]>>({});
  const ticketQuery = useQuery({ queryKey: ['portal', 'ticket', id], queryFn: () => apiRequest<Ticket>({ url: `/portal/tickets/${id}`, method: 'GET' }), enabled: Boolean(id) });
  const messagesQuery = useQuery({ queryKey: ['portal', 'ticket', id, 'messages'], queryFn: () => apiRequest<ApiPage<Message>>({ url: `/portal/tickets/${id}/messages`, method: 'GET', params: { per_page: 100 } }), enabled: Boolean(id) });
  const send = async (event: React.FormEvent) => { event.preventDefault(); setBusy(true); try { await apiRequest({ url: `/portal/tickets/${id}/messages`, method: 'POST', data: { body }, headers: { 'Idempotency-Key': crypto.randomUUID() } }); setBody(''); await queryClient.invalidateQueries({ queryKey: ['portal', 'ticket', id, 'messages'] }); notify('Reply sent.'); } catch { notify('The reply could not be sent. Please try again.', 'danger'); } finally { setBusy(false); } };
  const download = async (attachment: Attachment) => { try { const token = getPortalSession()?.token; const response = await httpClient.get(attachment.download_url, { responseType: 'blob', headers: token ? { Authorization: `Bearer ${token}` } : undefined }); const url = URL.createObjectURL(response.data); const anchor = document.createElement('a'); anchor.href = url; anchor.download = attachment.original_name; anchor.click(); URL.revokeObjectURL(url); } catch { notify('The attachment could not be downloaded.', 'danger'); } };
  const submitFeedback = async (event: React.FormEvent) => { event.preventDefault(); setBusy(true); setFeedbackErrors({}); try { await apiRequest({ url: `/portal/tickets/${id}/feedback`, method: 'POST', data: { score: Number(score), comment: comment || null }, headers: { 'Idempotency-Key': crypto.randomUUID() } }); setFeedbackSent(true); notify(t('portal.ticket_detail.feedback_submitted')); } catch (error) { const apiError = normaliseApiError(error); if (apiError.code === 'portal.feedback_already_submitted') setFeedbackSent(true); else setFeedbackErrors(Object.keys(apiError.fieldErrors).length ? apiError.fieldErrors : { form: [apiError.message] }); } finally { setBusy(false); } };
  return <div className="max-w-5xl mx-auto px-4 py-8"><AsyncBoundary query={ticketQuery}>{(ticket) => <><PageHeader eyebrow={ticket.reference} title={ticket.subject || t('portal.ticket_detail.title')} actions={<Badge tone="info">{ticket.status ?? 'Open'}</Badge>} /><div className="grid gap-5"><Card className="p-5"><h2 className="text-lg font-semibold">Conversation</h2><AsyncBoundary query={messagesQuery}>{(messages) => <div className="mt-4 space-y-3">{messages.items.length === 0 && <p className="text-sm text-slate-500">No messages are available yet.</p>}{messages.items.map((message) => <article key={message.uuid} className={`max-w-3xl rounded-xl p-4 ${message.author_type === 'customer' ? 'ms-auto bg-blue-50' : 'bg-slate-100'}`}><p className="whitespace-pre-wrap text-sm leading-6">{message.body}</p>{message.attachments && message.attachments.length > 0 && <ul className="mt-3 space-y-2" aria-label="Attachments">{message.attachments.map((attachment) => <li key={attachment.uuid}><Button variant="secondary" onClick={() => void download(attachment)}>Download {attachment.original_name}</Button></li>)}</ul>}<time className="mt-2 block text-xs text-slate-500">{new Date(message.created_at).toLocaleString()}</time></article>)}</div>}</AsyncBoundary><form onSubmit={send} className="mt-6 space-y-3"><Field label="Add a reply" required>{({ id: fieldId }) => <Textarea id={fieldId} value={body} onChange={(e) => setBody(e.target.value)} required />}</Field><div className="flex justify-end"><Button type="submit" busy={busy} disabled={!body.trim()}>Send reply</Button></div></form></Card><Card className="p-5"><h2 className="text-lg font-semibold">{t('portal.ticket_detail.feedback_section')}</h2>{feedbackSent ? <p className="mt-3 text-sm text-emerald-700" role="status">{t('portal.ticket_detail.feedback_submitted')}</p> : <form className="mt-4 grid gap-4" onSubmit={submitFeedback}><FormErrorSummary errors={feedbackErrors}/><Field label={t('portal.ticket_detail.feedback_prompt')} required error={feedbackErrors.score?.[0]}>{({id,describedBy}) => <Select id={id} aria-describedby={describedBy} value={score} onChange={(event) => setScore(event.target.value)}><option value="5">5 — Excellent</option><option value="4">4 — Good</option><option value="3">3 — Fair</option><option value="2">2 — Poor</option><option value="1">1 — Very poor</option></Select>}</Field><Field label="Comment" error={feedbackErrors.comment?.[0]}>{({id,describedBy}) => <Textarea id={id} aria-describedby={describedBy} maxLength={2000} value={comment} onChange={(event) => setComment(event.target.value)}/>}</Field><Button type="submit" busy={busy}>{t('portal.ticket_detail.feedback_submit')}</Button></form>}</Card></div></>}</AsyncBoundary></div>;
}
