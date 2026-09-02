import { forwardRef, type InputHTMLAttributes } from 'react';
import styles from './Input.module.css';
export interface InputProps extends InputHTMLAttributes<HTMLInputElement> { invalid?: boolean; }
export const Input = forwardRef<HTMLInputElement, InputProps>(function Input({ invalid, className, 'aria-invalid': ariaInvalid, ...props }, ref) { return <input ref={ref} aria-invalid={invalid || ariaInvalid} className={[styles.root, invalid && styles.invalid, className].filter(Boolean).join(' ')} {...props} />; });
