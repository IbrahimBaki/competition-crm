import { forwardRef, type ButtonHTMLAttributes, type ReactNode } from 'react';
import type { LucideIcon } from 'lucide-react';
import { icons } from '../../foundations/icons';
import styles from './Button.module.css';

export type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger';
export type ButtonSize = 'compact' | 'default' | 'touch';
export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
  leadingIcon?: LucideIcon;
  trailingIcon?: LucideIcon;
  loading?: boolean;
  children: ReactNode;
}
export const Button = forwardRef<HTMLButtonElement, ButtonProps>(function Button({ variant = 'primary', size = 'default', leadingIcon: LeadingIcon, trailingIcon: TrailingIcon, loading = false, disabled, className, children, ...props }, ref) {
  return <button ref={ref} className={[styles.root, styles[variant], styles[size], className].filter(Boolean).join(' ')} disabled={disabled || loading} aria-busy={loading || undefined} {...props}>
    {loading ? <icons.LoaderCircle className={styles.spinner} aria-hidden="true" /> : LeadingIcon ? <LeadingIcon className={styles.icon} aria-hidden="true" /> : null}
    <span className={styles.label}>{children}</span>
    {!loading && TrailingIcon ? <TrailingIcon className={styles.icon} aria-hidden="true" /> : null}
  </button>;
});
