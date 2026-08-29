import React, { useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/auth/AuthProvider';
import { useTranslation } from 'react-i18next';
import { normaliseApiError } from '@/api/http/errors';
import { Link } from 'react-router-dom';
import { Button, Card, Field, Input } from '@/components/ui';

export function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login } = useAuth();
  const { t } = useTranslation();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const from = (location.state?.from as string) || '/';

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      await login(email, password);
      navigate(from, { replace: true });
    } catch (err) {
      const normalised = normaliseApiError(err);
      setError(normalised.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <main className="min-h-screen flex items-center justify-center bg-slate-50 px-4 py-10">
      <div className="w-full max-w-md">
        <Card className="p-6 sm:p-8">
          <div className="text-center mb-8">
            <p className="ui-eyebrow">Operations workspace</p><h1 className="mt-2 text-2xl font-bold">{import.meta.env.VITE_APP_NAME}</h1>
            <h2 className="mt-2 text-base text-slate-600">{t('auth.title')}</h2>
          </div>

          {error && (
            <div role="alert" className="ui-alert ui-alert--danger mb-4">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <Field label={t('auth.email_label')} required>{({id}) => <Input
                id={id}
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                disabled={loading}
              />}</Field>

            <Field label={t('auth.password_label')} required>{({id}) => <Input
                id={id}
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                disabled={loading}
              />}</Field>

            <Button
              type="submit"
              disabled={loading}
              className="w-full"
              busy={loading}
            >
              {t('auth.sign_in_button')}
            </Button>
            <div className="flex justify-between text-sm"><Link to="/forgot-password" className="font-semibold text-blue-700">Forgot password?</Link><Link to="/portal/login" className="font-semibold text-blue-700">Customer portal</Link></div>
          </form>
        </Card>
      </div>
    </main>
  );
}
