import { useTranslation } from 'react-i18next';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { apiRequest } from '@/api/http/mutator';
import { normaliseApiError } from '@/api/http/errors';
import { Button, Card } from '@/components/ui';

export function PortalVerifyPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const token = params.get('token');
  const [state, setState] = useState<'waiting' | 'verifying' | 'verified' | 'error'>(token ? 'verifying' : 'waiting');
  const [message, setMessage] = useState('');

  useEffect(() => {
    if (!token) return;
    apiRequest({ url: '/portal/auth/verify', method: 'POST', data: { token } })
      .then(() => setState('verified'))
      .catch((error) => { setMessage(normaliseApiError(error).message); setState('error'); });
  }, [token]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <Card className="w-full max-w-lg p-8 text-center">
        <h1 className="text-2xl font-bold">Email verification</h1>
        <p className="mt-3 text-slate-600">{state === 'waiting' ? t('portal.verify.check_email') : state === 'verifying' ? 'Verifying your email…' : state === 'verified' ? 'Your email is verified. You can now sign in.' : message}</p>
        {(state === 'verified' || state === 'error' || state === 'waiting') && <Button onClick={() => navigate('/portal/login')} className="mt-6">Back to login</Button>}
      </Card>
    </div>
  );
}
