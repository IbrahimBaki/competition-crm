import { useState, type FormEvent } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Eye, EyeOff } from 'lucide-react';
import { useAuth } from '@/auth/AuthProvider';
import { normaliseApiError } from '@/api/http/errors';
import { Button } from '@/design-system/primitives/Button';
import { AuthErrorSummary, AuthField, AuthFrame, Input, styles } from './auth/AuthFrame';
import { LoginOperationsVisual } from './auth/LoginOperationsVisual';

export function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login } = useAuth();
  const { t } = useTranslation();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordVisible, setPasswordVisible] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const from = (location.state?.from as string) || '/';

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setError(null);
    setLoading(true);

    try {
      await login(email, password);
      navigate(from, { replace: true });
    } catch (caught) {
      setError(normaliseApiError(caught).message);
    } finally {
      setLoading(false);
    }
  };

  return <AuthFrame title={t('auth.title')} description={t('auth.login_description')} step={t('auth.login_step')} visual={<LoginOperationsVisual />}>
    <form className={styles.form} onSubmit={handleSubmit}>
      <AuthErrorSummary errors={error ? { form: [error] } : {}} />
      <AuthField label={t('auth.email_label')} required>{({ id, describedBy, invalid }) => <Input id={id} aria-describedby={describedBy} invalid={invalid} type="email" inputMode="email" autoComplete="username" dir="ltr" value={email} onChange={(event) => setEmail(event.target.value)} required disabled={loading} />}</AuthField>
      <AuthField label={t('auth.password_label')} required>{({ id, describedBy, invalid }) => <div className={styles.passwordControl}><Input id={id} aria-describedby={describedBy} invalid={invalid} type={passwordVisible ? 'text' : 'password'} autoComplete="current-password" dir="ltr" value={password} onChange={(event) => setPassword(event.target.value)} required disabled={loading} /><button type="button" className={styles.passwordToggle} aria-label={t(passwordVisible ? 'auth.hide_password' : 'auth.show_password')} aria-pressed={passwordVisible} onClick={() => setPasswordVisible((visible) => !visible)} disabled={loading}>{passwordVisible ? <EyeOff aria-hidden="true" /> : <Eye aria-hidden="true" />}</button></div>}</AuthField>
      <Button type="submit" loading={loading} className={styles.action}>{t('auth.sign_in_button')}</Button>
      <div className={styles.links}><Link className={styles.link} to="/forgot-password">{t('auth.forgot_password')}</Link><Link className={styles.link} to="/portal/login">{t('auth.customer_portal')}</Link></div>
    </form>
  </AuthFrame>;
}
