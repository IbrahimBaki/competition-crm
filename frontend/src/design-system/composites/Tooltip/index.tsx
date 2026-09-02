import * as RadixTooltip from '@radix-ui/react-tooltip';
import type { ComponentPropsWithoutRef } from 'react';
import styles from '../Overlay.module.css';
import { useV2PortalContainer } from '../../foundations/V2PortalBoundary';
export const TooltipProvider = RadixTooltip.Provider;
export const Tooltip = RadixTooltip.Root;
export const TooltipTrigger = RadixTooltip.Trigger;
export function TooltipContent({ className, sideOffset = 6, ...props }: ComponentPropsWithoutRef<typeof RadixTooltip.Content>) { const container = useV2PortalContainer(); return <RadixTooltip.Portal container={container}><RadixTooltip.Content className={[styles.tooltipContent, className].filter(Boolean).join(' ')} sideOffset={sideOffset} {...props} /></RadixTooltip.Portal>; }
