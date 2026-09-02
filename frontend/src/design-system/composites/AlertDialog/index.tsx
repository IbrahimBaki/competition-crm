import * as RadixAlertDialog from '@radix-ui/react-alert-dialog';
import type { ComponentPropsWithoutRef, ReactNode } from 'react';
import styles from '../Overlay.module.css';
import { useV2PortalContainer } from '../../foundations/V2PortalBoundary';
export const AlertDialog = RadixAlertDialog.Root;
export const AlertDialogTrigger = RadixAlertDialog.Trigger;
export const AlertDialogAction = RadixAlertDialog.Action;
export const AlertDialogCancel = RadixAlertDialog.Cancel;
export function AlertDialogContent({ title, description, children, className, ...props }: ComponentPropsWithoutRef<typeof RadixAlertDialog.Content> & { title: ReactNode; description?: ReactNode }) {
  const container = useV2PortalContainer();
  return <RadixAlertDialog.Portal container={container}><RadixAlertDialog.Overlay className={styles.overlay} /><RadixAlertDialog.Content className={[styles.alertContent, className].filter(Boolean).join(' ')} {...props}><RadixAlertDialog.Title className={styles.title}>{title}</RadixAlertDialog.Title>{description ? <RadixAlertDialog.Description className={styles.description}>{description}</RadixAlertDialog.Description> : null}{children}</RadixAlertDialog.Content></RadixAlertDialog.Portal>;
}
