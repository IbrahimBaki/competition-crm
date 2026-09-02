import type { ElementType, ReactNode } from 'react';
import styles from './StatePattern.module.css';
export interface StatePatternProps { title: ReactNode; description?: ReactNode; action?: ReactNode; mode?: 'region' | 'full'; headingAs?: ElementType; }
export function StatePattern({ title, description, action, mode = 'region', tone, headingAs: Heading = 'h2' }: StatePatternProps & { tone?: 'danger' | 'forbidden' }) { return <section className={[styles[mode], tone && styles[tone]].filter(Boolean).join(' ')}><Heading className={styles.title}>{title}</Heading>{description ? <p className={styles.description}>{description}</p> : null}{action ? <div className={styles.action}>{action}</div> : null}</section>; }
