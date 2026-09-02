import {
  createContext,
  forwardRef,
  useCallback,
  useContext,
  useEffect,
  useId,
  useRef,
  useState,
  type ButtonHTMLAttributes,
  type HTMLAttributes,
  type InputHTMLAttributes,
  type ReactNode,
  type SelectHTMLAttributes,
  type TextareaHTMLAttributes,
} from 'react';
import { useTranslation } from 'react-i18next';

const arabicFieldLabels: Record<string, string> = {
  'Active': 'نشط', 'Actions (JSON array)': 'الإجراءات (مصفوفة JSON)', 'Arabic article': 'المقالة العربية', 'Arabic name': 'الاسم بالعربية', 'Arabic title': 'العنوان بالعربية', 'Category': 'الفئة', 'Code': 'الرمز', 'Conditions (JSON array)': 'الشروط (مصفوفة JSON)', 'Cooldown minutes': 'دقائق الانتظار', 'Customer': 'العميل', 'Customer name': 'اسم العميل', 'Default priority': 'الأولوية الافتراضية', 'Department': 'الإدارة', 'Department ID': 'معرّف الإدارة', 'English article': 'المقالة الإنجليزية', 'English description': 'الوصف بالإنجليزية', 'English name': 'الاسم بالإنجليزية', 'English title': 'العنوان بالإنجليزية', 'Execution priority': 'أولوية التنفيذ', 'First response minutes': 'دقائق الاستجابة الأولى', 'Lifecycle': 'دورة الحياة', 'Message': 'الرسالة', 'Parent category UUID': 'معرّف الفئة الأب', 'Preferred language': 'اللغة المفضلة', 'Priority': 'الأولوية', 'Public key': 'المفتاح العام', 'Reason': 'السبب', 'Resolution minutes': 'دقائق الحل', 'Rule key': 'مفتاح القاعدة', 'Stop after this rule matches': 'توقف بعد تطابق هذه القاعدة', 'Subject': 'الموضوع', 'Target agent UUID': 'معرّف الموظف المستهدف', 'Target category UUID': 'معرّف الفئة المستهدفة', 'Target type': 'نوع الوجهة', 'Ticket category ID': 'معرّف فئة التذكرة', 'Token name': 'اسم الرمز', 'Transfer target': 'وجهة التحويل', 'Trigger': 'المحفّز', 'Visibility': 'مستوى الظهور',
};

const arabicPageTitles: Record<string, string> = {
  'Create ticket': 'إنشاء تذكرة',
  'Create customer': 'إنشاء عميل',
  'New knowledge article': 'مقالة معرفة جديدة',
};

export function cn(...values: Array<string | false | null | undefined>) {
  return values.filter(Boolean).join(' ');
}

type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger';

export const Button = forwardRef<HTMLButtonElement, ButtonHTMLAttributes<HTMLButtonElement> & { variant?: ButtonVariant; busy?: boolean }>(
  ({ className, variant = 'primary', busy, disabled, children, ...props }, ref) => (
    <button ref={ref} className={cn('ui-button', `ui-button--${variant}`, className)} disabled={disabled || busy} aria-busy={busy || undefined} {...props}>
      {busy && <span className="ui-spinner ui-spinner--small" aria-hidden="true" />}
      {children}
    </button>
  )
);
Button.displayName = 'Button';

export function Card({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('ui-card', className)} {...props} />;
}

export function Badge({ tone = 'neutral', className, ...props }: HTMLAttributes<HTMLSpanElement> & { tone?: 'neutral' | 'info' | 'success' | 'warning' | 'danger' }) {
  return <span className={cn('ui-badge', `ui-badge--${tone}`, className)} {...props} />;
}

interface FieldProps {
  label: ReactNode;
  error?: string;
  hint?: ReactNode;
  required?: boolean;
  children: (ids: { id: string; describedBy?: string }) => ReactNode;
}

export function Field({ label, error, hint, required, children }: FieldProps) {
  const { i18n } = useTranslation();
  const id = useId();
  const describedBy = error ? `${id}-error` : hint ? `${id}-hint` : undefined;
  const localizedLabel = typeof label === 'string' && i18n.language.startsWith('ar') ? arabicFieldLabels[label] ?? label : label;
  return (
    <div className="ui-field">
      <label htmlFor={id} className="ui-label">{localizedLabel}{required && <span className="ui-required" aria-hidden="true"> *</span>}</label>
      {children({ id, describedBy })}
      {hint && !error && <p id={`${id}-hint`} className="ui-hint">{hint}</p>}
      {error && <p id={`${id}-error`} className="ui-field-error" role="alert">{error}</p>}
    </div>
  );
}

export const Input = forwardRef<HTMLInputElement, InputHTMLAttributes<HTMLInputElement>>(({ className, ...props }, ref) => <input ref={ref} className={cn('ui-input', className)} {...props} />);
Input.displayName = 'Input';
export const Select = forwardRef<HTMLSelectElement, SelectHTMLAttributes<HTMLSelectElement>>(({ className, ...props }, ref) => <select ref={ref} className={cn('ui-input', className)} {...props} />);
Select.displayName = 'Select';
export const Textarea = forwardRef<HTMLTextAreaElement, TextareaHTMLAttributes<HTMLTextAreaElement>>(({ className, ...props }, ref) => <textarea ref={ref} className={cn('ui-input ui-textarea', className)} {...props} />);
Textarea.displayName = 'Textarea';

export function PageHeader({ title, description, actions, eyebrow }: { title: ReactNode; description?: ReactNode; actions?: ReactNode; eyebrow?: ReactNode }) {
  const { i18n } = useTranslation();
  const localizedTitle = typeof title === 'string' && i18n.language.startsWith('ar') ? arabicPageTitles[title] ?? title : title;
  return (
    <header className="ui-page-header">
      <div className="min-w-0">
        {eyebrow && <p className="ui-eyebrow">{eyebrow}</p>}
        <h1 className="ui-page-title" tabIndex={-1}>{localizedTitle}</h1>
        {description && <p className="ui-page-description">{description}</p>}
      </div>
      {actions && <div className="ui-page-actions">{actions}</div>}
    </header>
  );
}

export function FormErrorSummary({ title = 'Please correct the following', errors }: { title?: string; errors: Record<string, string[] | string> }) {
  const ref = useRef<HTMLDivElement>(null);
  const entries = Object.entries(errors);
  useEffect(() => { if (entries.length) ref.current?.focus(); }, [entries.length]);
  if (!entries.length) return null;
  return (
    <div ref={ref} tabIndex={-1} role="alert" className="ui-alert ui-alert--danger">
      <strong>{title}</strong>
      <ul>{entries.flatMap(([field, messages]) => (Array.isArray(messages) ? messages : [messages]).map((message) => <li key={`${field}-${message}`}>{message}</li>))}</ul>
    </div>
  );
}

export function Dialog({ open, title, description, children, onClose, footer, className }: { open: boolean; title: string; description?: string; children: ReactNode; onClose: () => void; footer?: ReactNode; className?: string }) {
  const titleId = useId();
  const descriptionId = useId();
  const panel = useRef<HTMLDivElement>(null);
  const closeRef = useRef(onClose);
  useEffect(() => { closeRef.current = onClose; }, [onClose]);
  useEffect(() => {
    if (!open) return;
    const previous = document.activeElement as HTMLElement | null;
    panel.current?.focus();
    const handler = (event: KeyboardEvent) => {
      if (event.key === 'Escape') { closeRef.current(); return; }
      if (event.key !== 'Tab' || !panel.current) return;
      const focusable = [...panel.current.querySelectorAll<HTMLElement>('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')];
      if (!focusable.length) { event.preventDefault(); panel.current.focus(); return; }
      const first = focusable[0]; const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    };
    document.addEventListener('keydown', handler);
    return () => { document.removeEventListener('keydown', handler); previous?.focus(); };
  }, [open]);
  if (!open) return null;
  return (
    <div className="ui-dialog-backdrop" role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget) onClose(); }}>
      <div ref={panel} tabIndex={-1} role="dialog" aria-modal="true" aria-labelledby={titleId} aria-describedby={description ? descriptionId : undefined} className={cn('ui-dialog', className)}>
        <div className="ui-dialog__header"><div><h2 id={titleId}>{title}</h2>{description && <p id={descriptionId}>{description}</p>}</div><Button variant="ghost" onClick={onClose} aria-label="Close dialog">×</Button></div>
        <div className="ui-dialog__content">{children}</div>
        {footer && <div className="ui-dialog__footer">{footer}</div>}
      </div>
    </div>
  );
}

interface Toast { id: number; message: string; tone: 'success' | 'danger' | 'info' }
const ToastContext = createContext<{ notify: (message: string, tone?: Toast['tone']) => void }>({ notify: () => undefined });

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);
  const notify = useCallback((message: string, tone: Toast['tone'] = 'success') => {
    const id = Date.now() + Math.random();
    setToasts((current) => [...current, { id, message, tone }]);
    window.setTimeout(() => setToasts((current) => current.filter((item) => item.id !== id)), 4500);
  }, []);
  return <ToastContext.Provider value={{ notify }}>{children}<div className="ui-toast-region" aria-live="polite">{toasts.map((toast) => <div className={cn('ui-toast', `ui-toast--${toast.tone}`)} key={toast.id}>{toast.message}<button onClick={() => setToasts((current) => current.filter((item) => item.id !== toast.id))} aria-label="Dismiss notification">×</button></div>)}</div></ToastContext.Provider>;
}

export const useToast = () => useContext(ToastContext);

export function Pagination({ page, totalPages, total, onPageChange, label = 'items' }: { page: number; totalPages: number; total: number; onPageChange: (page: number) => void; label?: string }) {
  return <nav className="ui-pagination" aria-label="Pagination"><p>{total} {label} · Page {page} of {Math.max(totalPages, 1)}</p><div><Button variant="secondary" disabled={page <= 1} onClick={() => onPageChange(page - 1)}>Previous</Button><Button variant="secondary" disabled={page >= totalPages} onClick={() => onPageChange(page + 1)}>Next</Button></div></nav>;
}

export function Skeleton({ lines = 4, variant = 'line' }: { lines?: number; variant?: 'line' | 'row' }) {
  return <div className={cn('ui-skeleton', variant === 'row' && 'ui-skeleton--row')} aria-label="Loading" role="status">{Array.from({ length: lines }, (_, index) => <span key={index} />)}</div>;
}
