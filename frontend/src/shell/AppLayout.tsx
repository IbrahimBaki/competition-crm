import { Outlet } from 'react-router-dom';
import { Sidebar } from './Sidebar';
import { TopBar } from './TopBar';

export function AppLayout() {
  const isRTL = document.documentElement.dir === 'rtl';

  return (
    <div className="flex h-screen bg-white">
      {/* Sidebar */}
      <div className={isRTL ? 'mr-0 ml-auto' : ''}>
        <Sidebar />
      </div>

      {/* Main content */}
      <div className="flex-1 flex flex-col">
        <TopBar />

        {/* Page content */}
        <main className="flex-1 overflow-auto">
          <div className="p-6">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
}
