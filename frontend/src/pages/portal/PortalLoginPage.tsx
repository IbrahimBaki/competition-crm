import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useState, type FormEvent } from 'react';
import { usePortalAuth } from '@/portal/auth/PortalAuthProvider';
import { Field, FormErrorSummary, Input, Button, Card } from '@/components/ui';
import { normaliseApiError } from '@/api/http/errors';

export function PortalLoginPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { login } = usePortalAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true); setErrors({});
    try { await login(email, password); navigate('/portal/tickets', { replace: true }); }
    catch (error) { const apiError = normaliseApiError(error); setErrors(Object.keys(apiError.fieldErrors).length ? apiError.fieldErrors : { form: [apiError.message] }); }
    finally { setBusy(false); }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
      <Card className="w-full max-w-md space-y-8 p-6 sm:p-8">
        <div>
          <h2 className="text-3xl font-bold text-gray-900">{t('portal.auth.login_title')}</h2>
        </div>
        <FormErrorSummary errors={errors} />
        <form onSubmit={handleSubmit} className="space-y-5" noValidate>
          <Field label={t('portal.auth.email_label')} error={errors.email?.[0]} required>{({ id, describedBy }) => <Input
              id={id} aria-describedby={describedBy}
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              autoComplete="email"
              required
            />}</Field>
          <Field label={t('portal.auth.password_label')} error={errors.password?.[0]} required>{({ id, describedBy }) => <Input
              id={id} aria-describedby={describedBy}
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              autoComplete="current-password"
              required
            />}</Field>
          <Button type="submit" busy={busy} className="w-full">
            {t('portal.auth.sign_in_button')}
          </Button>
        </form>
        <p className="text-center text-sm">
          {t('portal.auth.need_account')}{' '}
          <button onClick={() => navigate('/portal/register')} className="text-blue-600 hover:text-blue-700">
            {t('portal.auth.create_account_link')}
          </button>
        </p>
      </Card>
    </div>
  );
}
