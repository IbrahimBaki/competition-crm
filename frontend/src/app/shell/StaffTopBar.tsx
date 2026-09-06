import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useLocale } from '@/i18n/LocaleProvider';
import { useVisibleNavigation } from '@/shell/useVisibleNavigation';
import { IconButton } from '@/design-system/primitives/IconButton';
import { Drawer, DrawerClose, DrawerContent, DrawerTrigger } from '@/design-system/composites/Drawer';
import { icons, directionalIconClass } from '@/design-system/foundations/icons';
import { AccountMenu } from './AccountMenu';
import { NotificationsMenu } from './NotificationsMenu';
import { NavList } from './NavList';
import { usePageContext } from './usePageContext';
import styles from './StaffTopBar.module.css';

export function StaffTopBar() {
  const { t, i18n } = useTranslation();
  const { setLocale } = useLocale();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const navigation = useVisibleNavigation();
  const context = usePageContext();

  const toggleLocale = async () => {
    const next = i18n.language === 'en' ? 'ar' : 'en';
    await i18n.changeLanguage(next);
    setLocale(next);
  };

  return (
    <header className={styles.topbar}>
      <div className={styles.start}>
        <span className={styles.mobileOnly}>
          <Drawer open={drawerOpen} onOpenChange={setDrawerOpen}>
            <DrawerTrigger asChild>
              <IconButton icon={icons.Menu} label={t('shell.menu.open')} size="touch" aria-controls="staff-mobile-navigation" aria-expanded={drawerOpen} />
            </DrawerTrigger>
            <DrawerContent id="staff-mobile-navigation" title={t('shell.drawer.title')}>
              <DrawerClose asChild>
                <IconButton icon={icons.X} label={t('shell.menu.close')} size="touch" className={styles.drawerCloseBtn} />
              </DrawerClose>
              <NavList items={navigation} onNavigate={() => setDrawerOpen(false)} />
            </DrawerContent>
          </Drawer>
        </span>

        {context && (context.parentLabelKey && context.parentPath ? (
          <nav aria-label={t('shell.breadcrumb.landmark_label')} className={styles.context}>
            <Link to={context.parentPath} className={styles.crumb}>{t(context.parentLabelKey)}</Link>
            <icons.ChevronRight aria-hidden="true" className={[styles.crumbSep, directionalIconClass('ChevronRight')].filter(Boolean).join(' ')} />
            <span className={styles.contextCurrent} aria-current="page">{t(context.labelKey)}</span>
          </nav>
        ) : (
          <div className={styles.context}>
            <span className={styles.contextCurrent}>{t(context.labelKey)}</span>
          </div>
        ))}
      </div>

      <div className={styles.end}>
        <IconButton
          icon={icons.Globe}
          label={t(i18n.language === 'en' ? 'shell.locale.switch_to_arabic' : 'shell.locale.switch_to_english')}
          size="touch"
          onClick={() => { void toggleLocale(); }}
        />
        <NotificationsMenu />
        <AccountMenu />
      </div>
    </header>
  );
}
