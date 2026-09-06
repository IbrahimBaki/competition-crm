import { useTranslation } from 'react-i18next';
import { Check, MessageSquareText, Ticket, UsersRound } from 'lucide-react';
import styles from './LoginOperationsVisual.module.css';

const fragments = [
  { key: 'ticket', icon: Ticket, title: 'auth.login_visual_ticket', detail: 'auth.login_visual_ticket_detail', className: styles.ticket },
  { key: 'reply', icon: MessageSquareText, title: 'auth.login_visual_reply', detail: 'auth.login_visual_reply_detail', className: styles.reply },
  { key: 'team', icon: UsersRound, title: 'auth.login_visual_team', detail: 'auth.login_visual_team_detail', className: styles.team },
  { key: 'sla', icon: Check, title: 'auth.login_visual_sla', detail: 'auth.login_visual_sla_detail', className: styles.sla },
] as const;

export function LoginOperationsVisual() {
  const { t } = useTranslation();

  return <>
    <div className={styles.copy}>
      <div className={styles.identity}>{import.meta.env.VITE_APP_NAME}</div>
      <p className={styles.statement}>{t('auth.login_visual_statement')}</p>
      <p className={styles.access}>{t('auth.login_visual_access')}</p>
    </div>
    <div className={styles.network} aria-hidden="true">
      <svg className={styles.connections} viewBox="0 0 420 312" preserveAspectRatio="none" focusable="false">
        <path className={styles.route} pathLength="100" d="M34 80 C104 80 106 154 190 154 S286 88 386 88" />
        <path className={styles.route} pathLength="100" d="M34 231 C98 231 126 156 190 154 S286 226 386 226" />
        <path className={styles.routeFaint} d="M190 154 L190 278" />
        <circle className={styles.signal} r="4" pathLength="100"><animateMotion dur="7s" repeatCount="indefinite" path="M34 80 C104 80 106 154 190 154 S286 88 386 88" /></circle>
        <circle className={styles.signalSecondary} r="3" pathLength="100"><animateMotion dur="9s" begin="-4s" repeatCount="indefinite" path="M34 231 C98 231 126 156 190 154 S286 226 386 226" /></circle>
        <circle className={styles.centerNode} cx="190" cy="154" r="7" />
        <circle className={styles.centerRing} cx="190" cy="154" r="13" />
      </svg>
      {fragments.map(({ key, icon: Icon, title, detail, className }) => <div key={key} className={`${styles.fragment} ${className}`}>
        <Icon className={styles.fragmentIcon} />
        <div><span>{t(title)}</span><small>{t(detail)}</small></div>
      </div>)}
      <div className={styles.ledger}>
        <span /><span /><span /><span />
      </div>
    </div>
  </>;
}
