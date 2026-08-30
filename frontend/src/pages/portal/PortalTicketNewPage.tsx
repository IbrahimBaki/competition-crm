import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import React, { useState } from 'react';
import { apiRequest } from '@/api/http/mutator';
import { normaliseApiError } from '@/api/http/errors';
import { Button, Card, Field, FormErrorSummary, Input, PageHeader, Textarea } from '@/components/ui';

export function PortalTicketNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [subject, setSubject] = useState('');
  const [message, setMessage] = useState('');
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  const submit = async (event: React.FormEvent) => {
    event.preventDefault(); setBusy(true); setErrors({});
    try { const ticket = await apiRequest<{ uuid?: string }>({ url: '/portal/tickets', method: 'POST', data: { subject, message }, headers: { 'Idempotency-Key': crypto.randomUUID() } }); navigate(ticket.uuid ? `/portal/tickets/${ticket.uuid}` : '/portal/tickets'); }
    catch (error) { const apiError = normaliseApiError(error); setErrors(Object.keys(apiError.fieldErrors).length ? apiError.fieldErrors : { form: [apiError.message] }); }
    finally { setBusy(false); }
  };

  return (
    <div className="max-w-2xl mx-auto px-4 py-8">
      <PageHeader title={t('portal.ticket_new.title')} description="Tell us what happened and our support team will follow up." />
      <Card className="p-5 sm:p-7"><FormErrorSummary errors={errors} /><form onSubmit={submit} className="mt-4 space-y-5" noValidate><Field label={t('portal.ticket_new.subject_label')} error={errors.subject?.[0]} required>{({ id, describedBy }) => <Input id={id} aria-describedby={describedBy} value={subject} onChange={(e) => setSubject(e.target.value)} maxLength={255} required />}</Field><Field label={t('portal.ticket_new.description_label')} error={errors.message?.[0]} required>{({ id, describedBy }) => <Textarea id={id} aria-describedby={describedBy} value={message} onChange={(e) => setMessage(e.target.value)} required />}</Field><div className="flex justify-end gap-2"><Button type="button" variant="secondary" onClick={() => navigate('/portal/tickets')}>Cancel</Button><Button type="submit" busy={busy}>{t('portal.ticket_new.submit_button')}</Button></div></form></Card>
    </div>
  );
}
