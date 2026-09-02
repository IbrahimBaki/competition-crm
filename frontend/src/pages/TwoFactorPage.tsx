import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '@/auth/AuthProvider';
import { useTranslation } from 'react-i18next';
import { normaliseApiError } from '@/api/http/errors';
import { Button } from '@/design-system/primitives/Button';
import { AuthErrorSummary, AuthField, AuthFrame, Input, styles } from './auth/AuthFrame';

export function TwoFactorPage() {
  const navigate = useNavigate();
  const { completeTwoFactor } = useAuth();
  const { t } = useTranslation();

  const [code, setCode] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setError(null);
    setLoading(true);

    try {
      await completeTwoFactor(code);
      navigate('/', { replace: true });
    } catch (caught) {
      setError(normaliseApiError(caught).message);
    } finally {
      setLoading(false);
    }
  };

  return <AuthFrame title={t('auth.two_factor_title')} description={t('auth.two_factor_description')} step={t('auth.two_factor_step')}>
    <form className={styles.form} onSubmit={handleSubmit}>
      <AuthErrorSummary errors={error ? { form: [error] } : {}} />
      <AuthField label={t('auth.code_label')} required>{({ id, describedBy, invalid }) => <Input id={id} aria-describedby={describedBy} invalid={invalid} className={styles.code} type="text" inputMode="numeric" autoComplete="one-time-code" dir="ltr" value={code} onChange={(event) => setCode(event.target.value.trim().slice(0, 20))} maxLength={20} required disabled={loading} placeholder={t('auth.code_placeholder')} />}</AuthField>
      <Button type="submit" loading={loading} disabled={code.length < 6} className={styles.action}>{t('auth.verify_button')}</Button>
      <p className={styles.hint}>{t('auth.two_factor_recovery_hint')}</p>
    </form>
  </AuthFrame>;
}
