import { useEffect, useId, useRef, type ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { V2PortalBoundary } from '@/design-system/foundations/V2PortalBoundary';
import { Input } from '@/design-system/primitives/Input';
import { Label } from '@/design-system/primitives/Label';
import styles from './AuthFrame.module.css';

export function AuthFrame({ title, description, step, children }: { title: string; description: string; step: string; children: ReactNode }) {
  const { i18n, t } = useTranslation();
  const rtl = i18n.dir(i18n.language) === 'rtl';
  return <V2PortalBoundary dir={rtl ? 'rtl' : 'ltr'} lang={rtl ? 'ar' : 'en'}><main className={styles.page}><div className={styles.frame}><aside className={styles.context}><div className={styles.identity}>{import.meta.env.VITE_APP_NAME}</div><p className={styles.contextCopy}>{t('auth.context')}</p><p className={styles.step}>{step}</p></aside><section className={styles.work} aria-labelledby="auth-page-title"><h1 id="auth-page-title" className={styles.heading}>{title}</h1><p className={styles.description}>{description}</p><div className={styles.body}>{children}</div></section></div></main></V2PortalBoundary>;
}

export function AuthField({ label, error, hint, required, children }: { label: string; error?: string; hint?: string; required?: boolean; children: (props: { id: string; describedBy?: string; invalid: boolean }) => ReactNode }) {
  const id = useId();
  const hintId = `${id}-hint`;
  const errorId = `${id}-error`;
  const describedBy = error ? errorId : hint ? hintId : undefined;
  return <div className={styles.field}><Label htmlFor={id}>{label}{required ? <span className={styles.required} aria-hidden="true"> *</span> : null}</Label>{children({ id, describedBy, invalid: Boolean(error) })}{hint && !error ? <p id={hintId} className={styles.hint}>{hint}</p> : null}{error ? <p id={errorId} className={styles.fieldError} role="alert">{error}</p> : null}</div>;
}

export function AuthErrorSummary({ errors }: { errors: Record<string, string[] | string> }) {
  const ref = useRef<HTMLDivElement>(null);
  const entries = Object.entries(errors);
  useEffect(() => { if (entries.length) ref.current?.focus(); }, [entries.length]);
  if (!entries.length) return null;
  return <div ref={ref} tabIndex={-1} role="alert" className={styles.errorSummary}><ul>{entries.flatMap(([field, messages]) => (Array.isArray(messages) ? messages : [messages]).map((message) => <li key={`${field}-${message}`}>{message}</li>))}</ul></div>;
}

export { Input, styles };
