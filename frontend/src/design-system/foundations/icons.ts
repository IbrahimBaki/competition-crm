import { AlertTriangle, BarChart3, Bell, BookOpen, Check, ChevronDown, ChevronLeft, ChevronRight, Globe, Home, Inbox, Info, LayoutDashboard, LoaderCircle, LogOut, Menu, ShieldCheck, Search, Users, UserRound, X, type LucideIcon } from 'lucide-react';

export const iconSizes = { inline: 16, control: 20, prominent: 24 } as const;
export const icons = { AlertTriangle, BarChart3, Bell, BookOpen, Check, ChevronDown, ChevronLeft, ChevronRight, Globe, Home, Inbox, Info, LayoutDashboard, LoaderCircle, LogOut, Menu, ShieldCheck, Search, Users, UserRound, X } as const satisfies Record<string, LucideIcon>;
export type DirectionalIcon = 'ChevronLeft' | 'ChevronRight';
export function directionalIconClass(direction: DirectionalIcon): string {
  return direction === 'ChevronLeft' || direction === 'ChevronRight' ? 'ds-directional-icon' : '';
}
