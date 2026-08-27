import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '@/auth/AuthProvider';
import { useTranslation } from 'react-i18next';
import { normaliseApiError } from '@/api/http/errors';

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
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="w-full max-w-md">
        <div className="bg-white py-8 px-6 shadow rounded-lg">
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
            <div>
              <label htmlFor="code" className="block text-sm font-medium text-gray-700 mb-1">
                {t('auth.code_label')}
              </label>
              <input
                id="code"
                type="text"
                inputMode="numeric"
                value={code}
                onChange={(e) => setCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
                maxLength={6}
                required
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-2xl tracking-widest"
                disabled={loading}
                placeholder="000000"
              />
            </div>

            <button
              type="submit"
              disabled={loading || code.length !== 6}
              className="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition"
            >
              {loading ? t('auth.verify_button') + '...' : t('auth.verify_button')}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}
