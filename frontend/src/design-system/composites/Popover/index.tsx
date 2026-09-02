import * as RadixPopover from '@radix-ui/react-popover';
import type { ComponentPropsWithoutRef } from 'react';
import styles from '../Overlay.module.css';
import { useV2PortalContainer } from '../../foundations/V2PortalBoundary';
export const Popover = RadixPopover.Root;
export const PopoverTrigger = RadixPopover.Trigger;
export const PopoverClose = RadixPopover.Close;
export function PopoverContent({ className, sideOffset = 6, ...props }: ComponentPropsWithoutRef<typeof RadixPopover.Content>) { const container = useV2PortalContainer(); return <RadixPopover.Portal container={container}><RadixPopover.Content className={[styles.popoverContent, className].filter(Boolean).join(' ')} sideOffset={sideOffset} {...props} /></RadixPopover.Portal>; }
