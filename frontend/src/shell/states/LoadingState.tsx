interface LoadingStateProps {
  rows?: number;
  label?: string;
}

export function LoadingState({ rows = 3, label = 'Loading...' }: LoadingStateProps) {
  return (
    <div className="space-y-4">
      {Array.from({ length: rows }).map((_, i) => (
        <div key={i} className="h-12 bg-gray-200 rounded animate-pulse" />
      ))}
      <p className="text-center text-gray-600 text-sm mt-4">{label}</p>
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
