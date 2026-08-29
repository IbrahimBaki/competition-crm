import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '@/auth/AuthProvider';
import { useTranslation } from 'react-i18next';
import { normaliseApiError } from '@/api/http/errors';
import { Button, Card, Field, Input } from '@/components/ui';

export function TwoFactorPage() {
  const navigate = useNavigate();
  const { completeTwoFactor } = useAuth();
  const { t } = useTranslation();

  const [code, setCode] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      await completeTwoFactor(code);
      navigate('/', { replace: true });
    } catch (err) {
      const normalised = normaliseApiError(err);
      setError(normalised.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <main className="min-h-screen flex items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-md">
        <Card className="p-6 sm:p-8">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-gray-900">{t('auth.two_factor_title')}</h1>
            <p className="mt-2 text-gray-600">{t('auth.two_factor_description')}</p>
          </div>

          {error && (
            <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <Field label={t('auth.code_label')} required>{({id}) => <Input
                id={id}
                type="text"
                inputMode="numeric"
                value={code}
                onChange={(e) => setCode(e.target.value.trim().slice(0, 20))}
                maxLength={20}
                autoComplete="one-time-code"
                required
                className="text-center text-2xl tracking-widest"
                disabled={loading}
                placeholder="000000"
              />}</Field>

            <Button
              type="submit"
              disabled={loading || code.length < 6}
              className="w-full" busy={loading}
            >
              {t('auth.verify_button')}
            </Button>
          </form>
          <p className="mt-4 text-center text-xs text-slate-500">Enter the six-digit authenticator code or one unused recovery code.</p>
        </Card>
      </div>
    </main>
  );
}
