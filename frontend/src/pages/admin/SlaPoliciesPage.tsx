import { useTranslation } from 'react-i18next';
import React, { useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { CollectionPage } from '@/features/operations/CollectionPage';
import { Button, Dialog, Field, FormErrorSummary, Input, useToast } from '@/components/ui';
import { apiRequest } from '@/api/http/mutator';
import { normaliseApiError } from '@/api/http/errors';
import { usePermissions } from '@/auth/usePermissions';
import { PERMISSIONS } from '@/auth/permissions';

export function SlaPoliciesPage() {
  const { t } = useTranslation();
  const { can } = usePermissions();
  const client = useQueryClient();
  const { notify } = useToast();
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Record<string, unknown> | null>(null);
  const [nameEn, setNameEn] = useState(''); const [nameAr, setNameAr] = useState('');
  const [responseMinutes, setResponseMinutes] = useState(''); const [resolutionMinutes, setResolutionMinutes] = useState('');
  const [errors, setErrors] = useState<Record<string, string[]>>({}); const [busy, setBusy] = useState(false);
  const submit = async (event: React.FormEvent) => { event.preventDefault(); setBusy(true); setErrors({}); const targets = [{ type: 'first_response', minutes: Number(responseMinutes) }, ...(resolutionMinutes ? [{ type: 'resolution', minutes: Number(resolutionMinutes) }] : [])]; try { await apiRequest({ url: editing ? `/sla/policies/${String(editing.id ?? editing.uuid)}` : '/sla/policies', method: editing ? 'PUT' : 'POST', data: editing ? { name: { en: nameEn, ar: nameAr } } : { name: { en: nameEn, ar: nameAr }, targets }, headers: { 'Idempotency-Key': crypto.randomUUID() } }); await client.invalidateQueries({ queryKey: ['sla-policies'] }); setOpen(false); setEditing(null); notify(editing ? 'SLA policy updated.' : 'SLA policy created.'); } catch (error) { const apiError = normaliseApiError(error); setErrors(Object.keys(apiError.fieldErrors).length ? apiError.fieldErrors : { form: [apiError.message] }); } finally { setBusy(false); } };

  return (
    <><CollectionPage title={t('admin.sla.title')} description="Define business-time response and resolution commitments." endpoint="/sla/policies" queryKey={['sla-policies']} columns={[{ key: 'name', label: 'Policy' }, { key: 'is_active', label: 'State' }, { key: 'updated_at', label: 'Updated' }]} actions={can(PERMISSIONS.SLA_POLICIES_MANAGE) ? <Button onClick={() => { setEditing(null); setNameEn(''); setNameAr(''); setResponseMinutes(''); setResolutionMinutes(''); setOpen(true); }}>New policy</Button> : undefined} rowActions={can(PERMISSIONS.SLA_POLICIES_MANAGE) ? (row) => <Button variant="secondary" onClick={() => { const name = String(row.name ?? ''); setEditing(row); setNameEn(name); setNameAr(name); setOpen(true); }}>Edit</Button> : undefined} deleteEndpoint={can(PERMISSIONS.SLA_POLICIES_MANAGE) ? (row) => `/sla/policies/${String(row.id ?? row.uuid)}` : undefined}/><Dialog open={open} onClose={() => setOpen(false)} title={editing ? 'Edit SLA policy' : 'New SLA policy'} footer={<><Button variant="secondary" onClick={() => setOpen(false)}>Cancel</Button><Button type="submit" form="sla-form" busy={busy}>{editing ? 'Save changes' : 'Create policy'}</Button></>}><FormErrorSummary errors={errors}/><form id="sla-form" onSubmit={submit} className="mt-4 grid gap-4"><Field label="English name" error={errors['name.en']?.[0]} required>{({ id }) => <Input id={id} value={nameEn} onChange={(e) => setNameEn(e.target.value)} required/>}</Field><Field label="Arabic name" error={errors['name.ar']?.[0]} required>{({ id }) => <Input id={id} dir="rtl" value={nameAr} onChange={(e) => setNameAr(e.target.value)} required/>}</Field>{!editing && <><Field label="First response minutes" error={errors['targets.0.minutes']?.[0]} required>{({ id }) => <Input id={id} type="number" min="1" value={responseMinutes} onChange={(e) => setResponseMinutes(e.target.value)} required/>}</Field><Field label="Resolution minutes">{({ id }) => <Input id={id} type="number" min="1" value={resolutionMinutes} onChange={(e) => setResolutionMinutes(e.target.value)}/>}</Field></>}</form></Dialog></>
  );
}
