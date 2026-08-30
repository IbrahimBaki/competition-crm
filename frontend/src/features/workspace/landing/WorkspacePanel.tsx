import type { ReactNode } from 'react';
import { Link } from 'react-router-dom';

interface WorkspacePanelProps {
  title: string;
  count?: number;
  viewAllHref?: string;
  viewAllLabel?: string;
  children: ReactNode;
}

export function WorkspacePanel({ title, count, viewAllHref, viewAllLabel, children }: WorkspacePanelProps) {
  return (
    <section className="flex max-h-[28rem] flex-col rounded border border-gray-200 bg-white">
      <header className="flex items-center justify-between border-b border-gray-100 px-4 py-3">
        <h2 className="text-sm font-semibold text-gray-800">
          {title}
          {count !== undefined && <span className="ms-2 text-xs font-normal text-gray-400">{count}</span>}
        </h2>
        {viewAllHref && (
          <Link to={viewAllHref} className="text-xs font-medium text-blue-600 hover:text-blue-700">
            {viewAllLabel}
          </Link>
        )}
      </header>
      <div className="flex-1 overflow-y-auto p-3">{children}</div>
    </section>
  );
}
