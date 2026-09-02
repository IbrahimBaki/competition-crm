import * as RadixDialog from '@radix-ui/react-dialog';
import type { ComponentPropsWithoutRef, ReactNode } from 'react';
import styles from '../Overlay.module.css';
import { useV2PortalContainer } from '../../foundations/V2PortalBoundary';
export const Dialog = RadixDialog.Root;
export const DialogTrigger = RadixDialog.Trigger;
export const DialogClose = RadixDialog.Close;
export function DialogContent({ title, description, children, className, ...props }: ComponentPropsWithoutRef<typeof RadixDialog.Content> & { title: ReactNode; description?: ReactNode }) {
  const container = useV2PortalContainer();
  return <RadixDialog.Portal container={container}><RadixDialog.Overlay className={styles.overlay} /><RadixDialog.Content className={[styles.dialogContent, className].filter(Boolean).join(' ')} {...props}><RadixDialog.Title className={styles.title}>{title}</RadixDialog.Title>{description ? <RadixDialog.Description className={styles.description}>{description}</RadixDialog.Description> : null}{children}</RadixDialog.Content></RadixDialog.Portal>;
}
