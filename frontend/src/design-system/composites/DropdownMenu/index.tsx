import * as RadixDropdownMenu from '@radix-ui/react-dropdown-menu';
import type { ComponentPropsWithoutRef } from 'react';
import styles from '../Overlay.module.css';
import { useV2PortalContainer } from '../../foundations/V2PortalBoundary';
export const DropdownMenu = RadixDropdownMenu.Root;
export const DropdownMenuTrigger = RadixDropdownMenu.Trigger;
export function DropdownMenuContent({ className, sideOffset = 6, ...props }: ComponentPropsWithoutRef<typeof RadixDropdownMenu.Content>) { const container = useV2PortalContainer(); return <RadixDropdownMenu.Portal container={container}><RadixDropdownMenu.Content className={[styles.menuContent, styles.dropdownContent, className].filter(Boolean).join(' ')} sideOffset={sideOffset} {...props} /></RadixDropdownMenu.Portal>; }
export function DropdownMenuItem({ className, ...props }: ComponentPropsWithoutRef<typeof RadixDropdownMenu.Item>) { return <RadixDropdownMenu.Item className={[styles.menuItem, className].filter(Boolean).join(' ')} {...props} />; }
