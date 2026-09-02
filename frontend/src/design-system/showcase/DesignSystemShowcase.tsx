import { useState } from 'react';
import { AlertTriangle, Check, ChevronDown, Info, Search, X } from 'lucide-react';
import { Button } from '../primitives/Button';
import { IconButton } from '../primitives/IconButton';
import { Input } from '../primitives/Input';
import { Textarea } from '../primitives/Textarea';
import { Label } from '../primitives/Label';
import { Badge } from '../primitives/Badge';
import { Dialog, DialogClose, DialogContent, DialogTrigger } from '../composites/Dialog';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogTrigger } from '../composites/AlertDialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '../composites/DropdownMenu';
import { Popover, PopoverContent, PopoverTrigger } from '../composites/Popover';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '../composites/Tooltip';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../composites/Tabs';
import { EmptyState } from '../patterns/EmptyState';
import { ErrorState } from '../patterns/ErrorState';
import { ForbiddenState } from '../patterns/ForbiddenState';
import { LoadingState } from '../patterns/LoadingState';
import styles from './DesignSystemShowcase.module.css';

export function DesignSystemShowcase() {
  const [open, setOpen] = useState(false);
  return <main className={styles.root}>
    <div className={styles.stack}>
      <section className={styles.band}><h1>Signal Ledger foundation</h1><p>Ticket #SUP-28431 · Payment confirmation not received · SLA breach in 18 minutes</p><p className="ds-numeric">SUP-28431 · 2026-09-02 · 18:00 · agent@support.example</p><div className={styles.swatches}>{['canvas', 'base', 'raised', 'selected'].map((surface) => <span key={surface} className={[styles.swatch, styles[surface]].join(' ')}>{surface}</span>)}</div></section>
      <section className={styles.band}><h2>Typography and actions</h2><div className={styles.row}><Button leadingIcon={Check}>Primary</Button><Button variant="secondary">Secondary</Button><Button variant="ghost">Ghost</Button><Button variant="danger">Danger</Button><Button loading>Loading</Button><IconButton icon={Search} label="Search" /><IconButton icon={ChevronDown} label="More options" directional /></div></section>
      <section className={styles.band}><h2>Inputs and status</h2><div className={styles.field}><Label htmlFor="showcase-input">Ticket subject</Label><Input id="showcase-input" value="Payment confirmation not received" readOnly /><Input aria-label="Error example" invalid value="Required field" readOnly /><Input aria-label="Disabled example" disabled value="Assigned to Customer Support" /><Textarea aria-label="Message" value="لم يتم استلام تأكيد الدفع. الرجاء مراجعة العملية وإرسال التحديث إلى العميل." readOnly /><div className={styles.row}><Badge>Neutral</Badge><Badge tone="info">Info</Badge><Badge tone="success">Success</Badge><Badge tone="warning">SLA breach in 18 minutes</Badge><Badge tone="danger">Overdue</Badge></div></div></section>
      <section className={styles.band}><h2>Overlays and tabs</h2><TooltipProvider><div className={styles.row}><Dialog open={open} onOpenChange={setOpen}><DialogTrigger asChild><Button variant="secondary">Open dialog</Button></DialogTrigger><DialogContent title="Ticket activity" description="Assigned to Customer Support"><p>Ticket #SUP-28431</p><DialogClose asChild><Button variant="ghost">Close</Button></DialogClose></DialogContent></Dialog><AlertDialog><AlertDialogTrigger asChild><Button variant="danger">Escalate</Button></AlertDialogTrigger><AlertDialogContent title="Escalate ticket" description="This action notifies the on-call queue."><div className={styles.row}><AlertDialogCancel asChild><Button variant="ghost">Cancel</Button></AlertDialogCancel><AlertDialogAction asChild><Button>Confirm</Button></AlertDialogAction></div></AlertDialogContent></AlertDialog><DropdownMenu><DropdownMenuTrigger asChild><Button variant="secondary" trailingIcon={ChevronDown}>Menu</Button></DropdownMenuTrigger><DropdownMenuContent><DropdownMenuItem>Assign ticket</DropdownMenuItem><DropdownMenuItem>View customer</DropdownMenuItem></DropdownMenuContent></DropdownMenu><Popover><PopoverTrigger asChild><Button variant="ghost">Details</Button></PopoverTrigger><PopoverContent>Customer requested a payment receipt.</PopoverContent></Popover><Tooltip><TooltipTrigger asChild><IconButton icon={Info} label="Ticket information" /></TooltipTrigger><TooltipContent>Ticket information</TooltipContent></Tooltip></div></TooltipProvider><Tabs defaultValue="one"><TabsList><TabsTrigger value="one">Activity</TabsTrigger><TabsTrigger value="two">Properties</TabsTrigger></TabsList><TabsContent value="one">Latest customer message</TabsContent><TabsContent value="two">Assigned to Customer Support</TabsContent></Tabs></section>
      <section className={styles.band}><h2>States</h2><EmptyState title="Empty region" description="A compact empty state stays inside its work region." action={<Button variant="secondary">Action</Button>} /><LoadingState title="Loading region" description="Loading preserves the surrounding context." /><ErrorState title="Problem loading region" description="Recovery stays local to the work region." action={<Button variant="secondary" leadingIcon={Info}>Retry</Button>} /><ForbiddenState title="Access unavailable" description="This region explains the scope without exposing data." action={<Button variant="ghost" leadingIcon={AlertTriangle}>Learn more</Button>} /></section>
      <section lang="ar" dir="rtl" className={styles.band}><h2>مثال عربي</h2><div className={styles.row}><Button leadingIcon={Check}>إجراء أساسي</Button><Badge tone="warning">يتطلب مراجعة</Badge><IconButton icon={X} label="إغلاق" /></div><Input aria-label="الموضوع" placeholder="الموضوع" /></section>
    </div>
    </main>;
}
