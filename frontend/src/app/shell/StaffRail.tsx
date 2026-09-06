import { useVisibleNavigation } from '@/shell/useVisibleNavigation';
import { NavList } from './NavList';
import styles from './StaffRail.module.css';

/** Persistent desktop/tablet navigation rail. Hidden at <=768px in favour of the mobile drawer. */
export function StaffRail() {
  const navigation = useVisibleNavigation();
  return (
    <aside className={styles.rail}>
      <div className={styles.brand}>{import.meta.env.VITE_APP_NAME}</div>
      <NavList items={navigation} />
    </aside>
  );
}
