import { NormalisedApiError } from '@/api/http/errors';

interface ErrorStateProps {
  error: NormalisedApiError;
  onRetry?: () => void;
}

export function ErrorState({ error, onRetry }: ErrorStateProps) {
  const copyToClipboard = () => {
    if (error.requestId) {
      navigator.clipboard.writeText(error.requestId);
    }
  };

  return (
    <div className="flex flex-col items-center justify-center py-12 px-4">
      <div className="text-red-500 mb-4">
        <svg
          className="h-12 w-12 mx-auto"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>
      <h3 className="text-lg font-medium text-gray-900 mb-2">Something went wrong</h3>
      <p className="text-gray-600 mb-4 text-center">{error.message}</p>

      {error.requestId && (
        <div
          className="text-xs text-gray-500 bg-gray-100 px-3 py-2 rounded cursor-pointer hover:bg-gray-200 transition"
          onClick={copyToClipboard}
          title="Click to copy"
        >
          Request ID: {error.requestId}
        </div>
      )}

      {onRetry && (
        <button
          onClick={onRetry}
          className="mt-6 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
        >
          Retry
        </button>
      )}
    </div>
  );
}
