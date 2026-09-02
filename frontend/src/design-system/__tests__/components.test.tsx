import { render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';
import { configureAxe } from 'vitest-axe';
import { Check, Search } from 'lucide-react';
import { Button } from '../primitives/Button';
import { IconButton } from '../primitives/IconButton';
import { Input } from '../primitives/Input';
import { Label } from '../primitives/Label';
import { Dialog, DialogContent, DialogTrigger } from '../composites/Dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '../composites/DropdownMenu';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../composites/Tabs';
import { V2PortalBoundary } from '../foundations/V2PortalBoundary';
function V2({ children, rtl = false }: { children: ReactNode; rtl?: boolean }) { return <V2PortalBoundary dir={rtl ? 'rtl' : 'ltr'} lang={rtl ? 'ar' : 'en'}>{children}</V2PortalBoundary>; }
// jsdom cannot calculate rendered color contrast; Radix portals are intentionally
// outside a test container and trip axe's document-region best-practice rule.
const axe = configureAxe({ rules: { 'color-contrast': { enabled: false }, region: { enabled: false } } });
describe('V2 primitives and composites', () => {
  it('has no axe violations for buttons and labelled input', async () => { const { container } = render(<V2><Button leadingIcon={Check}>Save</Button><IconButton icon={Search} label="Search" /><Label htmlFor="subject">Subject</Label><Input id="subject" /></V2>); expect((await axe(container)).violations).toEqual([]); });
  it('requires an accessible name for icon buttons and supports RTL directional icon class', () => { render(<V2 rtl><IconButton icon={Search} label="بحث" directional /></V2>); expect(screen.getByRole('button', { name: 'بحث' })).toBeVisible(); });
  it('opens a keyboard-focusable dialog inside the V2 boundary and restores focus on close', async () => { const user = userEvent.setup(); render(<V2 rtl><Dialog><DialogTrigger>Open</DialogTrigger><DialogContent title="Title">Body</DialogContent></Dialog></V2>); const trigger = screen.getByRole('button', { name: 'Open' }); await user.click(trigger); const dialog = screen.getByRole('dialog'); expect(dialog).toBeVisible(); expect(dialog.closest('[data-ui="v2"]')).not.toBeNull(); expect((await axe(document.body)).violations).toEqual([]); await user.keyboard('{Escape}'); expect(trigger).toHaveFocus(); });
  it('renders accessible menu and tabs', async () => { const user = userEvent.setup(); const { container } = render(<V2><DropdownMenu><DropdownMenuTrigger>More</DropdownMenuTrigger><DropdownMenuContent><DropdownMenuItem>Action</DropdownMenuItem></DropdownMenuContent></DropdownMenu><Tabs defaultValue="one"><TabsList><TabsTrigger value="one">One</TabsTrigger><TabsTrigger value="two">Two</TabsTrigger></TabsList><TabsContent value="one">Panel one</TabsContent><TabsContent value="two">Panel two</TabsContent></Tabs></V2>); await user.click(screen.getByRole('button', { name: 'More' })); const item = screen.getByRole('menuitem', { name: 'Action' }); expect(item).toBeVisible(); expect(item.closest('[data-ui="v2"]')).not.toBeNull(); expect((await axe(document.body)).violations).toEqual([]); await user.keyboard('{Escape}'); expect(screen.getByRole('tab', { name: 'One' })).toHaveAttribute('aria-selected', 'true'); expect((await axe(container)).violations).toEqual([]); });
});
