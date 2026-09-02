import { icons } from '../../foundations/icons';
import styles from './Spinner.module.css';
export function Spinner({ label, size = 'default' }: { label?: string; size?: 'compact' | 'default' }) { return <span className={[styles.root, styles[size]].join(' ')} role={label ? 'status' : undefined} aria-label={label}><icons.LoaderCircle aria-hidden="true" /></span>; }
