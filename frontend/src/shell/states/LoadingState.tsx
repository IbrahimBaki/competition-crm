import { Skeleton } from '@/components/ui';

interface LoadingStateProps {
  rows?: number;
  label?: string;
}

export function LoadingState({ rows = 3, label = 'Loading...' }: LoadingStateProps) {
  return (
    <div>
      <Skeleton lines={rows} variant="row" />
      <p className="mt-4 text-center text-sm" style={{ color: 'var(--color-muted-foreground)' }}>{label}</p>
    </div>
  );
}

export function FullPageLoading() {
  return (
    <div className="flex items-center justify-center h-screen bg-white">
      <div className="text-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto mb-4" />
        <p className="text-gray-600">Loading...</p>
      </div>
    </div>
  );
}

export function TableSkeleton({ columns = 4, rows = 5 }: { columns?: number; rows?: number }) {
  return (
    <div className="ui-card overflow-hidden" aria-label="Loading table">
      <div className="grid gap-3 border-b border-slate-200 bg-slate-50 p-4" style={{ gridTemplateColumns: `repeat(${columns}, minmax(6rem, 1fr))` }}>
        {Array.from({ length: columns }, (_, index) => <Skeleton key={index} lines={1} />)}
      </div>
      <Skeleton lines={rows} variant="row" />
      <div className="border-t border-slate-200 p-4"><Skeleton lines={1} /></div>
    </div>
  );
}

export function DetailPageSkeleton() {
  return (
    <div className="grid gap-5" aria-label="Loading details">
      <div className="ui-card p-6"><Skeleton lines={4} /></div>
      <div className="grid gap-5 lg:grid-cols-2"><div className="ui-card p-5"><Skeleton lines={5} /></div><div className="ui-card p-5"><Skeleton lines={5} /></div></div>
    </div>
  );
}

export function FormSkeleton() {
  return (
    <div className="ui-card grid gap-5 p-6" aria-label="Loading form">
      <div className="grid gap-5 sm:grid-cols-2"><Skeleton lines={3} variant="row" /><Skeleton lines={3} variant="row" /></div>
      <Skeleton lines={2} variant="row" />
      <div className="max-w-xs"><Skeleton lines={1} variant="row" /></div>
    </div>
  );
}
