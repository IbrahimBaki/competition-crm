import * as RadixDialog from '@radix-ui/react-dialog';
import type { ComponentPropsWithoutRef, ReactNode } from 'react';
import styles from '../Overlay.module.css';
import { useV2PortalContainer } from '../../foundations/V2PortalBoundary';
export const Drawer = RadixDialog.Root;
export const DrawerTrigger = RadixDialog.Trigger;
export const DrawerClose = RadixDialog.Close;
export function DrawerContent({ title, children, className, ...props }: ComponentPropsWithoutRef<typeof RadixDialog.Content> & { title: ReactNode }) {
  const container = useV2PortalContainer();
  return <RadixDialog.Portal container={container}><RadixDialog.Overlay className={styles.overlay} /><RadixDialog.Content className={[styles.drawerContent, className].filter(Boolean).join(' ')} {...props}><RadixDialog.Title className={styles.drawerTitle}>{title}</RadixDialog.Title>{children}</RadixDialog.Content></RadixDialog.Portal>;
}
