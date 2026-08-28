import { useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import { keepPreviousData, type UseQueryResult } from '@tanstack/react-query';
import { useGetRoles } from '@/api/generated/security/security';
import type { ApiPage } from '@/api/http/envelope';
import type { Role } from '../types';
import { toRole } from '../api/wire';

const DEFAULT_PER_PAGE = 25;

export interface RoleListState {
  page: number;
  perPage: number;
  sort?: string;
}

function parseState(searchParams: URLSearchParams): RoleListState {
  return {
    page: Math.max(1, parseInt(searchParams.get('page') ?? '1', 10) || 1),
    perPage: parseInt(searchParams.get('per_page') ?? String(DEFAULT_PER_PAGE), 10) || DEFAULT_PER_PAGE,
    sort: searchParams.get('sort') ?? undefined,
  };
}

function serializeState(state: RoleListState): URLSearchParams {
  const params = new URLSearchParams();
  params.set('page', String(state.page));
  params.set('per_page', String(state.perPage));
  if (state.sort) params.set('sort', state.sort);
  return params;
}

export function buildRoleQueryParams(state: RoleListState) {
  return { page: state.page, per_page: state.perPage, sort: state.sort };
}

export function useRoleListQuery() {
  const [searchParams, setSearchParams] = useSearchParams();
  const state = useMemo(() => parseState(searchParams), [searchParams]);

  const setState = (updates: Partial<RoleListState>) => {
    const next: RoleListState = { ...state, ...updates };
    if (updates.page === undefined) next.page = 1;
    setSearchParams(serializeState(next));
  };

  const setSort = (sort: string | undefined) => setState({ sort });
  const params = useMemo(() => buildRoleQueryParams(state), [state]);

  const rawQuery = useGetRoles(params, {
    query: { placeholderData: keepPreviousData },
  }) as unknown as UseQueryResult<ApiPage<Record<string, unknown>>, unknown>;

  const query = useMemo(() => {
    if (!rawQuery.data) return rawQuery as unknown as UseQueryResult<ApiPage<Role>, unknown>;
    return {
      ...rawQuery,
      data: { items: rawQuery.data.items.map(toRole), meta: rawQuery.data.meta },
    } as unknown as UseQueryResult<ApiPage<Role>, unknown>;
  }, [rawQuery]);

  return { state, setState, setSort, query };
}
