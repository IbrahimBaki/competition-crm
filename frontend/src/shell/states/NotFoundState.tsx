import { Link } from 'react-router-dom';

export function NotFoundState() {
  return (
    <div className="flex flex-col items-center justify-center py-12 px-4">
      <h1 className="text-5xl font-bold text-gray-900 mb-2">404</h1>
      <h2 className="text-xl font-medium text-gray-700 mb-4">Page Not Found</h2>
      <p className="text-gray-600 mb-6 text-center">
        The page you're looking for doesn't exist or has been moved.
      </p>
      <Link to="/" className="text-blue-600 hover:text-blue-700 font-medium">
        Back to Dashboard
      </Link>
    </div>
  );
}
