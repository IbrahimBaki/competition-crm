import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { useGetDepartments } from '@/api/generated/organization/organization';
import type { ApiPage } from '@/api/http/envelope';
import { pickBilingual, type BilingualValue } from './bilingual';

interface DepartmentRow {
  id: string;
  name: BilingualValue;
}

/**
 * `department_id` on a ticket is the only related-entity id on the raw ticket
 * list rows that is a resolvable UUID (departments use a UUID primary key).
 * Fetch the (small, org-wide) department list once and map id -> localized
 * name, rather than a per-row lookup.
 */
export function useDepartmentNames() {
  const { i18n } = useTranslation();
  const query = useGetDepartments({ per_page: 100 }) as unknown as {
    data?: ApiPage<DepartmentRow>;
    isLoading: boolean;
  };

  const byId = useMemo(() => {
    const map = new Map<string, string>();
    for (const department of query.data?.items ?? []) {
      map.set(department.id, pickBilingual(department.name, i18n.language));
    }
    return map;
  }, [i18n.language, query.data]);

  return { byId, isLoading: query.isLoading };
}
