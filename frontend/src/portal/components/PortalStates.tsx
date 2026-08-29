import type { ReactNode } from 'react';
import type { UseQueryResult } from '@tanstack/react-query';
import { Button, Card, Skeleton } from '@/components/ui';

export function PortalEmptyState({ title, description, action }: { title: string; description?: string; action?: ReactNode }) {
  return <div className="px-5 py-12 text-center"><h2 className="text-lg font-semibold">{title}</h2>{description && <p className="mx-auto mt-2 max-w-md text-sm text-slate-600">{description}</p>}{action && <div className="mt-5">{action}</div>}</div>;
}

export function PortalAsyncBoundary<T>({ query, children, isEmpty, empty }: { query: UseQueryResult<T, Error>; children: (data: T) => ReactNode; isEmpty?: (data: T) => boolean; empty?: ReactNode }) {
  if (query.isPending) return <Card className="p-6"><Skeleton lines={5}/></Card>;
  if (query.isError) return <Card className="p-6"><div role="alert" className="ui-alert ui-alert--danger"><strong>We couldn’t load this information.</strong><p className="mt-1 text-sm">{query.error.message}</p><Button className="mt-4" variant="secondary" onClick={() => query.refetch()}>Try again</Button></div></Card>;
  if (query.data === undefined) return null;
  if (isEmpty?.(query.data)) return <>{empty ?? <PortalEmptyState title="Nothing here yet"/>}</>;
  return <>{children(query.data)}</>;
}
