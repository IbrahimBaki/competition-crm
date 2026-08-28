import { useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import { keepPreviousData, type UseQueryResult } from '@tanstack/react-query';
import { useGetUsers } from '@/api/generated/security/security';
import type { ApiPage } from '@/api/http/envelope';
import type { AdminUser } from '../types';
import { toAdminUser } from '../api/wire';

const DEFAULT_PER_PAGE = 25;

export interface UserListState {
  page: number;
  perPage: number;
  sort?: string;
}

function parseState(searchParams: URLSearchParams): UserListState {
  return {
    page: Math.max(1, parseInt(searchParams.get('page') ?? '1', 10) || 1),
    perPage: parseInt(searchParams.get('per_page') ?? String(DEFAULT_PER_PAGE), 10) || DEFAULT_PER_PAGE,
    sort: searchParams.get('sort') ?? undefined,
  };
}

function serializeState(state: UserListState): URLSearchParams {
  const params = new URLSearchParams();
  params.set('page', String(state.page));
  params.set('per_page', String(state.perPage));
  if (state.sort) params.set('sort', state.sort);
  return params;
}

export function buildUserQueryParams(state: UserListState) {
  return { page: state.page, per_page: state.perPage, sort: state.sort };
}

export function useUserListQuery() {
  const [searchParams, setSearchParams] = useSearchParams();
  const state = useMemo(() => parseState(searchParams), [searchParams]);

  const setState = (updates: Partial<UserListState>) => {
    const next: UserListState = { ...state, ...updates };
    if (updates.page === undefined) next.page = 1;
    setSearchParams(serializeState(next));
  };

  const setSort = (sort: string | undefined) => setState({ sort });
  const params = useMemo(() => buildUserQueryParams(state), [state]);

  const rawQuery = useGetUsers(params, {
    query: { placeholderData: keepPreviousData },
  }) as unknown as UseQueryResult<ApiPage<Record<string, unknown>>, unknown>;

  const query = useMemo(() => {
    if (!rawQuery.data) return rawQuery as unknown as UseQueryResult<ApiPage<AdminUser>, unknown>;
    return {
      ...rawQuery,
      data: { items: rawQuery.data.items.map(toAdminUser), meta: rawQuery.data.meta },
    } as unknown as UseQueryResult<ApiPage<AdminUser>, unknown>;
  }, [rawQuery]);

  return { state, setState, setSort, query };
}
