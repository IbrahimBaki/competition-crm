import { Link } from 'react-router-dom';

export function ForbiddenState() {
  return (
    <div className="flex flex-col items-center justify-center py-12 px-4">
      <div className="text-yellow-600 mb-4">
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
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
          />
        </svg>
      </div>
      <h3 className="text-lg font-medium text-gray-900 mb-2">Access Denied</h3>
      <p className="text-gray-600 mb-6 text-center">
        You don't have permission to access this resource.
      </p>
      <Link to="/" className="text-blue-600 hover:text-blue-700 font-medium">
        Back to Dashboard
      </Link>
    </div>
  );
}
