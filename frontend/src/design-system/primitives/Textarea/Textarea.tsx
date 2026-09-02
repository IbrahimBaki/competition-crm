import { forwardRef, type TextareaHTMLAttributes } from 'react';
import styles from './Textarea.module.css';
export interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> { invalid?: boolean; }
export const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(function Textarea({ invalid, className, 'aria-invalid': ariaInvalid, ...props }, ref) { return <textarea ref={ref} aria-invalid={invalid || ariaInvalid} className={[styles.root, invalid && styles.invalid, className].filter(Boolean).join(' ')} {...props} />; });
