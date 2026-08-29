import { PageHeader, Card, Button, Badge } from '@/components/ui';
import { usePortalAuth } from '@/portal/auth/PortalAuthProvider';

export function PortalAccountPage() {
  const { user, logout } = usePortalAuth();
  return <div className="mx-auto max-w-3xl px-4 py-8"><PageHeader title="Account" description="Your verified support portal identity."/><Card className="p-6"><dl className="grid gap-5 sm:grid-cols-2"><div><dt className="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt><dd className="mt-1 font-medium">{user?.email}</dd></div><div><dt className="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt><dd className="mt-1"><Badge tone="success">Verified</Badge></dd></div></dl><div className="mt-8 border-t border-slate-200 pt-5"><Button variant="secondary" onClick={() => void logout()}>Sign out</Button></div></Card></div>;
}
