import { useTranslation } from 'react-i18next';
import type { ReactNode } from 'react';
import type { CustomerDetail } from '../types';
import { CustomerStatusBadge } from './CustomerStatusBadge';
import styles from './CustomerRecordV2.module.css';

interface CustomerHeaderProps {
  customer: CustomerDetail;
  actions?: ReactNode;
}

export function CustomerHeader({ customer, actions }: CustomerHeaderProps) {
  const { t } = useTranslation();

  return (
    <header className={styles.header}>
      <div className={styles.headerTop}>
      <div>
        <div className={styles.titleLine}>
          <h1 className={styles.title}>{customer.name}</h1>
          <CustomerStatusBadge status={customer.status} label={t(`customers.status.${customer.status}`)} />
        </div>
        <p className={styles.context}>
          {customer.companyAccount ? customer.companyAccount.name : t('customers.detail.no_company_account')}
          {customer.companyAccount?.serviceTier && (
            <span>
              · {t('customers.detail.service_tier', { tier: customer.companyAccount.serviceTier })}
            </span>
          )}
        </p>
        {customer.status === 'blocked' && customer.blockedReason && (
          <p className={`${styles.context} ${styles.blocked}`}>
            {t('customers.detail.blocked_reason', { reason: customer.blockedReason })}
          </p>
        )}
      </div>

      {actions && <div className={styles.actions}>{actions}</div>}
      </div>
    </header>
  );
}
