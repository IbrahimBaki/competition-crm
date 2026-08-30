import React, { useEffect, useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import { normaliseApiError } from '@/api/http/errors';
import { Button, Dialog, Field, FormErrorSummary, Input, Select, Textarea, useToast } from '@/components/ui';

export interface ResourceField {
  key: string;
  label: string;
  kind?: 'text' | 'number' | 'textarea' | 'select' | 'checkbox' | 'json' | 'csv';
  required?: boolean;
  options?: Array<{ value: string; label: string }>;
  placeholder?: string;
}

interface Props {
  open: boolean;
  title: string;
  endpoint: string;
  method?: 'POST' | 'PATCH' | 'PUT';
  queryKey: readonly unknown[];
  fields: ResourceField[];
  initial?: Record<string, unknown>;
  transform?: (values: Record<string, unknown>) => Record<string, unknown>;
  onClose: () => void;
  onSaved?: (data: unknown) => void;
}

function initialValue(field: ResourceField, initial?: Record<string, unknown>): string | boolean {
  const value = initial?.[field.key];
  if (field.kind === 'checkbox') return Boolean(value);
  if (field.kind === 'json') return value === undefined ? (field.required ? '[]' : '') : JSON.stringify(value, null, 2);
  if (field.kind === 'csv') return Array.isArray(value) ? value.join(', ') : String(value ?? '');
  if (typeof value === 'object' && value !== null) return String((value as Record<string, unknown>).en ?? '');
  return String(value ?? '');
}

export function ResourceFormDialog({ open, title, endpoint, method = 'POST', queryKey, fields, initial, transform, onClose, onSaved }: Props) {
  const client = useQueryClient();
  const { notify } = useToast();
  const [values, setValues] = useState<Record<string, string | boolean>>({});
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (open) setValues(Object.fromEntries(fields.map((field) => [field.key, initialValue(field, initial)])));
  }, [open, fields, initial]);

  const submit = async (event: React.FormEvent) => {
    event.preventDefault(); setBusy(true); setErrors({});
    try {
      const payload: Record<string, unknown> = {};
      for (const field of fields) {
        const value = values[field.key];
        if (field.kind === 'number') payload[field.key] = value === '' ? null : Number(value);
        else if (field.kind === 'checkbox') payload[field.key] = Boolean(value);
        else if (field.kind === 'json') payload[field.key] = value ? JSON.parse(String(value)) : [];
        else if (field.kind === 'csv') payload[field.key] = String(value ?? '').split(',').map((item) => item.trim()).filter(Boolean);
        else payload[field.key] = value;
      }
      const result = await apiRequest({ url: endpoint, method, data: transform ? transform(payload) : payload, headers: { 'Idempotency-Key': crypto.randomUUID() } });
      await client.invalidateQueries({ queryKey });
      notify(`${title} saved.`); onSaved?.(result); onClose();
    } catch (error) {
      if (error instanceof SyntaxError) setErrors({ form: ['One of the JSON fields is not valid JSON.'] });
      else { const apiError = normaliseApiError(error); setErrors(Object.keys(apiError.fieldErrors).length ? apiError.fieldErrors : { form: [apiError.message] }); }
    } finally { setBusy(false); }
  };

  return <Dialog open={open} title={title} description="Complete the required fields, then save your changes." onClose={onClose} footer={<><Button variant="secondary" onClick={onClose}>Cancel</Button><Button type="submit" form="resource-form" busy={busy}>Save</Button></>}><FormErrorSummary errors={errors}/><form id="resource-form" className="mt-4 grid gap-4" onSubmit={submit}>{fields.map((field) => <Field key={field.key} label={field.label} required={field.required} error={errors[field.key]?.[0]}>{({id,describedBy}) => field.kind === 'textarea' || field.kind === 'json' ? <Textarea id={id} aria-describedby={describedBy} rows={field.kind === 'json' ? 7 : 4} value={String(values[field.key] ?? '')} placeholder={field.placeholder} required={field.required} onChange={(event) => setValues((current) => ({...current,[field.key]:event.target.value}))}/> : field.kind === 'select' ? <Select id={id} aria-describedby={describedBy} value={String(values[field.key] ?? '')} required={field.required} onChange={(event) => setValues((current) => ({...current,[field.key]:event.target.value}))}><option value="">Select</option>{field.options?.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</Select> : field.kind === 'checkbox' ? <input id={id} aria-describedby={describedBy} className="h-5 w-5" type="checkbox" checked={Boolean(values[field.key])} onChange={(event) => setValues((current) => ({...current,[field.key]:event.target.checked}))}/> : <Input id={id} aria-describedby={describedBy} type={field.kind === 'number' ? 'number' : 'text'} value={String(values[field.key] ?? '')} placeholder={field.placeholder} required={field.required} onChange={(event) => setValues((current) => ({...current,[field.key]:event.target.value}))}/>}</Field>)}</form></Dialog>;
}
