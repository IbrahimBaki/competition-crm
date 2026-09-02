import { AlertTriangle, Check, ChevronDown, ChevronLeft, ChevronRight, Info, LoaderCircle, Search, X, type LucideIcon } from 'lucide-react';

export const iconSizes = { inline: 16, control: 20, prominent: 24 } as const;
export const icons = { AlertTriangle, Check, ChevronDown, ChevronLeft, ChevronRight, Info, LoaderCircle, Search, X } as const satisfies Record<string, LucideIcon>;
export type DirectionalIcon = 'ChevronLeft' | 'ChevronRight';
export function directionalIconClass(direction: DirectionalIcon): string {
  return direction === 'ChevronLeft' || direction === 'ChevronRight' ? 'ds-directional-icon' : '';
}
