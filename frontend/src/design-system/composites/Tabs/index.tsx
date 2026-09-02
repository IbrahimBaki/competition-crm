import * as RadixTabs from '@radix-ui/react-tabs';
import type { ComponentPropsWithoutRef } from 'react';
import styles from '../Overlay.module.css';
export const Tabs = RadixTabs.Root;
export function TabsList({ className, ...props }: ComponentPropsWithoutRef<typeof RadixTabs.List>) { return <RadixTabs.List className={[styles.tabsList, className].filter(Boolean).join(' ')} {...props} />; }
export function TabsTrigger({ className, ...props }: ComponentPropsWithoutRef<typeof RadixTabs.Trigger>) { return <RadixTabs.Trigger className={[styles.tabsTrigger, className].filter(Boolean).join(' ')} {...props} />; }
export function TabsContent({ className, ...props }: ComponentPropsWithoutRef<typeof RadixTabs.Content>) { return <RadixTabs.Content className={[styles.tabsContent, className].filter(Boolean).join(' ')} {...props} />; }
