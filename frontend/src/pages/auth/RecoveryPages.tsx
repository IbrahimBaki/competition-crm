import { useState, type FormEvent } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { acceptInvitation, requestPasswordReset, resetPassword } from '@/auth/api/recovery';
import { normaliseApiError } from '@/api/http/errors';
import { Button } from '@/design-system/primitives/Button';
import { AuthErrorSummary, AuthField, AuthFrame, Input, styles } from './AuthFrame';
import { LoginOperationsVisual } from './LoginOperationsVisual';

type Errors = Record<string, string[]>;

function toErrors(error: unknown): Errors {
  const apiError = normaliseApiError(error);
  return Object.keys(apiError.fieldErrors).length ? apiError.fieldErrors : { form: [apiError.message] };
}

export function ForgotPasswordPage() {
  const { t } = useTranslation();
  const [email, setEmail] = useState('');
  const [busy, setBusy] = useState(false);
  const [sent, setSent] = useState(false);
  const [errors, setErrors] = useState<Errors>({});
  const submit = async (event: FormEvent) => { event.preventDefault(); setBusy(true); setErrors({}); try { await requestPasswordReset(email); setSent(true); } catch (error) { setErrors(toErrors(error)); } finally { setBusy(false); } };
  return <AuthFrame title={t('auth.forgot_title')} description={t('auth.forgot_description')} step={t('auth.recovery_step')} visual={<LoginOperationsVisual />}>
    {sent ? <p className={styles.successNotice} role="status">{t('auth.forgot_sent')}</p> : <form className={styles.form} onSubmit={submit}><AuthErrorSummary errors={errors} /><AuthField label={t('auth.work_email_label')} error={errors.email?.[0]} required>{({ id, describedBy, invalid }) => <Input id={id} aria-describedby={describedBy} invalid={invalid} type="email" inputMode="email" autoComplete="email" dir="ltr" value={email} onChange={(event) => setEmail(event.target.value)} required disabled={busy} />}</AuthField><Button type="submit" loading={busy} className={styles.action}>{t('auth.send_reset_link')}</Button></form>}
    <Link className={`${styles.link} ${styles.singleLink}`} to="/login">{t('auth.back_to_sign_in')}</Link>
  </AuthFrame>;
}

export function ResetPasswordPage() {
  const { t } = useTranslation();
  const [params] = useSearchParams();
  const navigate = useNavigate();
  const [email, setEmail] = useState(params.get('email') ?? '');
  const [password, setPassword] = useState('');
  const [confirmation, setConfirmation] = useState('');
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState<Errors>({});
  const submit = async (event: FormEvent) => { event.preventDefault(); setErrors({}); if (password !== confirmation) { setErrors({ password_confirmation: [t('auth.password_mismatch')] }); return; } setBusy(true); try { await resetPassword({ email, password, password_confirmation: confirmation, token: params.get('token') ?? '' }); navigate('/login', { replace: true }); } catch (error) { setErrors(toErrors(error)); } finally { setBusy(false); } };
  return <AuthFrame title={t('auth.reset_title')} description={t('auth.reset_description')} step={t('auth.recovery_step')}><form className={styles.form} onSubmit={submit}><AuthErrorSummary errors={errors} /><AuthField label={t('auth.email_label')} error={errors.email?.[0]} required>{({ id, describedBy, invalid }) => <Input id={id} aria-describedby={describedBy} invalid={invalid} type="email" inputMode="email" autoComplete="email" dir="ltr" value={email} onChange={(event) => setEmail(event.target.value)} required disabled={busy} />}</AuthField><AuthField label={t('auth.new_password_label')} error={errors.password?.[0]} required>{({ id, describedBy, invalid }) => <Input id={id} aria-describedby={describedBy} invalid={invalid} type="password" minLength={12} autoComplete="new-password" dir="ltr" value={password} onChange={(event) => setPassword(event.target.value)} required disabled={busy} />}</AuthField><AuthField label={t('auth.confirm_password_label')} error={errors.password_confirmation?.[0]} required>{({ id, describedBy, invalid }) => <Input id={id} aria-describedby={describedBy} invalid={invalid} type="password" autoComplete="new-password" dir="ltr" value={confirmation} onChange={(event) => setConfirmation(event.target.value)} required disabled={busy} />}</AuthField><Button type="submit" loading={busy} className={styles.action}>{t('auth.reset_password')}</Button></form></AuthFrame>;
}

export function InvitationAcceptancePage() {
  const { t } = useTranslation();
  const { token = '' } = useParams();
  const navigate = useNavigate();
  const [name, setName] = useState('');
  const [password, setPassword] = useState('');
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState<Errors>({});
  const submit = async (event: FormEvent) => { event.preventDefault(); setBusy(true); setErrors({}); try { await acceptInvitation(token, { token, name, password }); navigate('/login', { replace: true }); } catch (error) { setErrors(toErrors(error)); } finally { setBusy(false); } };
  return <AuthFrame title={t('auth.invitation_title')} description={t('auth.invitation_description')} step={t('auth.invitation_step')}><form className={styles.form} onSubmit={submit}><AuthErrorSummary errors={errors} /><AuthField label={t('auth.full_name_label')} error={errors.name?.[0]} required>{({ id, describedBy, invalid }) => <Input id={id} aria-describedby={describedBy} invalid={invalid} autoComplete="name" value={name} onChange={(event) => setName(event.target.value)} required disabled={busy} />}</AuthField><AuthField label={t('auth.password_label')} hint={t('auth.password_hint')} error={errors.password?.[0]} required>{({ id, describedBy, invalid }) => <Input id={id} aria-describedby={describedBy} invalid={invalid} type="password" minLength={12} autoComplete="new-password" dir="ltr" value={password} onChange={(event) => setPassword(event.target.value)} required disabled={busy} />}</AuthField><Button type="submit" loading={busy} className={styles.action}>{t('auth.create_account')}</Button></form></AuthFrame>;
}
