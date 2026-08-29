import { useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { apiRequest } from '@/api/http/mutator';
import { normaliseApiError } from '@/api/http/errors';
import { Button, Card, Field, FormErrorSummary, Input } from '@/components/ui';

export function PortalRegisterPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmation, setConfirmation] = useState('');
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setErrors({});
    if (password !== confirmation) { setErrors({ password_confirmation: ['Passwords do not match.'] }); return; }
    setBusy(true);
    try { await apiRequest({ url: '/portal/auth/register', method: 'POST', data: { email, password, password_confirmation: confirmation, locale: localStorage.getItem('locale') ?? 'en' } }); navigate('/portal/verify', { state: { email } }); }
    catch (error) { const apiError = normaliseApiError(error); setErrors(Object.keys(apiError.fieldErrors).length ? apiError.fieldErrors : { form: [apiError.message] }); }
    finally { setBusy(false); }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
      <Card className="w-full max-w-md space-y-6 p-6 sm:p-8">
        <h2 className="text-3xl font-bold text-gray-900">{t('portal.register.title')}</h2>
        <FormErrorSummary errors={errors} />
        <form onSubmit={handleSubmit} className="space-y-5" noValidate>
          <Field label={t('portal.register.email_label')} error={errors.email?.[0]} required>{({ id, describedBy }) => <Input id={id} aria-describedby={describedBy} type="email" autoComplete="email" value={email} onChange={(e) => setEmail(e.target.value)} required />}</Field>
          <Field label={t('portal.register.password_label')} hint="Use at least 12 characters." error={errors.password?.[0]} required>{({ id, describedBy }) => <Input id={id} aria-describedby={describedBy} type="password" minLength={12} autoComplete="new-password" value={password} onChange={(e) => setPassword(e.target.value)} required />}</Field>
          <Field label="Confirm password" error={errors.password_confirmation?.[0]} required>{({ id, describedBy }) => <Input id={id} aria-describedby={describedBy} type="password" autoComplete="new-password" value={confirmation} onChange={(e) => setConfirmation(e.target.value)} required />}</Field>
          <Button type="submit" busy={busy} className="w-full">{t('portal.register.submit_button')}</Button>
        </form>
      </Card>
    </div>
  );
}
