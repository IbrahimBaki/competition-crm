import { Outlet, useLocation } from 'react-router-dom';
import { Sidebar } from './Sidebar';
import { TopBar } from './TopBar';
import { useEffect, useState } from 'react';

export function AppLayout() {
  const [menuOpen, setMenuOpen] = useState(false);
  const location = useLocation();
  useEffect(() => { document.getElementById('main-content')?.focus(); }, [location.pathname]);

  return (
    <div className="app-shell">
      <a className="skip-link" href="#main-content">Skip to main content</a>
      <button className={`mobile-scrim ${menuOpen ? 'is-open' : ''}`} onClick={() => setMenuOpen(false)} aria-label="Close navigation" />
      <Sidebar open={menuOpen} onNavigate={() => setMenuOpen(false)} />
      <div className="app-column">
        <TopBar onMenu={() => setMenuOpen(true)} />
        <main id="main-content" className="app-main" tabIndex={-1}>
          <div className="app-content">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
}
