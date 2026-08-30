import { useTranslation } from 'react-i18next';
import type { ReactNode } from 'react';
import type { CustomerDetail } from '../types';
import { CustomerStatusBadge } from './CustomerStatusBadge';

interface CustomerHeaderProps {
  customer: CustomerDetail;
  actions?: ReactNode;
}

export function CustomerHeader({ customer, actions }: CustomerHeaderProps) {
  const { t } = useTranslation();

  return (
    <div className="flex flex-wrap items-start justify-between gap-3 rounded border border-gray-200 bg-white p-4">
      <div>
        <div className="flex items-center gap-2">
          <h1 className="text-2xl font-bold text-gray-900">{customer.name}</h1>
          <CustomerStatusBadge status={customer.status} label={t(`customers.status.${customer.status}`)} />
        </div>
        <p className="mt-1 text-sm text-gray-600">
          {customer.companyAccount ? customer.companyAccount.name : t('customers.detail.no_company_account')}
          {customer.companyAccount?.serviceTier && (
            <span className="ms-2 text-gray-400">
              · {t('customers.detail.service_tier', { tier: customer.companyAccount.serviceTier })}
            </span>
          )}
        </p>
        {customer.status === 'blocked' && customer.blockedReason && (
          <p className="mt-1 text-sm text-red-700">
            {t('customers.detail.blocked_reason', { reason: customer.blockedReason })}
          </p>
        )}
      </div>

      {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
    </div>
  );
}
