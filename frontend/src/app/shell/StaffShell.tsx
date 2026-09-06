import { useEffect } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { V2PortalBoundary } from '@/design-system/foundations/V2PortalBoundary';
import { StaffRail } from './StaffRail';
import { StaffTopBar } from './StaffTopBar';
import styles from './StaffShell.module.css';

/**
 * Authenticated staff application shell. V2 chrome (rail, topbar, their
 * overlays) lives in dedicated V2PortalBoundary instances; <main> — where
 * still-mostly-V1 route content renders via <Outlet/> — is deliberately
 * outside all of them. See StaffShell.module.css for why that split exists.
 */
export function StaffShell() {
  const { t, i18n } = useTranslation();
  const location = useLocation();
  const rtl = i18n.dir(i18n.language) === 'rtl';
  const dir = rtl ? 'rtl' : 'ltr';
  const lang = rtl ? 'ar' : 'en';

  useEffect(() => {
    document.getElementById('main-content')?.focus();
  }, [location.pathname]);

  return (
    <div className={styles.root}>
      <V2PortalBoundary dir={dir} lang={lang}>
        <a className={styles.skipLink} href="#main-content">{t('shell.skip_to_content')}</a>
      </V2PortalBoundary>

      <V2PortalBoundary dir={dir} lang={lang} className={styles.railBoundary}>
        <StaffRail />
      </V2PortalBoundary>

      <div className={styles.column}>
        <V2PortalBoundary dir={dir} lang={lang} className={styles.topbarBoundary}>
          <StaffTopBar />
        </V2PortalBoundary>

        <main id="main-content" tabIndex={-1} className={`app-main ${styles.main}`}>
          <div className="app-content">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
}
