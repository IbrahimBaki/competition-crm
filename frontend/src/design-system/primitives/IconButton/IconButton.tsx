import { forwardRef, type ButtonHTMLAttributes } from 'react';
import type { LucideIcon } from 'lucide-react';
import styles from './IconButton.module.css';
export interface IconButtonProps extends Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'children'> { icon: LucideIcon; label: string; size?: 'default' | 'touch'; directional?: boolean; }
export const IconButton = forwardRef<HTMLButtonElement, IconButtonProps>(function IconButton({ icon: Icon, label, size = 'default', directional = false, className, ...props }, ref) {
  return <button ref={ref} type="button" className={[styles.root, styles[size], className].filter(Boolean).join(' ')} aria-label={label} {...props}><Icon aria-hidden="true" className={directional ? 'ds-directional-icon' : undefined} /></button>;
});
