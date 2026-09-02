import type { HTMLAttributes } from 'react';
import styles from './VisuallyHidden.module.css';
export function VisuallyHidden(props: HTMLAttributes<HTMLSpanElement>) { return <span className={styles.root} {...props} />; }
