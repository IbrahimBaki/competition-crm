import { useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import { keepPreviousData, type UseQueryResult } from '@tanstack/react-query';
import {
  useGetTickets,
  useGetTicketQueueMine,
  useGetTicketQueueDepartment,
} from '@/api/generated/ticketing/ticketing';
import type { ApiPage } from '@/api/http/envelope';
import type { TicketListRow } from '../types';

// Allowed sort/filter fields mirror app/Domains/Ticketing/Http/Controllers/TicketController.php
// and TicketQueueController.php (CollectionQuerySpec) exactly. A filter or sort
// field outside these sets is rejected by the backend with a 422.
export const TICKET_SORT_FIELDS = ['created_at', 'updated_at', 'priority', 'status', 'reference'] as const;
export type TicketSortField = (typeof TICKET_SORT_FIELDS)[number];

export const TICKET_FILTER_KEYS = [
  'status',
  'priority',
  'department',
  'assignee',
  'customer',
  'category',
  'tag',
] as const;
export type TicketFilterKey = (typeof TICKET_FILTER_KEYS)[number];

export type TicketQueueMode = 'all' | 'mine' | 'department';

export const TICKET_FILTER_KEYS_BY_MODE: Record<TicketQueueMode, readonly TicketFilterKey[]> = {
  all: TICKET_FILTER_KEYS,
  mine: ['status', 'priority', 'department', 'category', 'tag'],
  department: ['status', 'priority', 'assignee', 'category', 'tag'],
};

export type TicketFilters = Partial<Record<TicketFilterKey, string>>;

export interface TicketListState {
  page: number;
  perPage: number;
  sort?: string; // e.g. "-created_at"; matches sortParameter.ts encoding
  filters: TicketFilters;
  search?: string;
}

const DEFAULT_PER_PAGE = 25;

function parseState(searchParams: URLSearchParams, allowedFilterKeys: readonly TicketFilterKey[]): TicketListState {
  const filters: TicketFilters = {};
  for (const key of allowedFilterKeys) {
    const value = searchParams.get(key);
    if (value) filters[key] = value;
  }

  return {
    page: Math.max(1, parseInt(searchParams.get('page') ?? '1', 10) || 1),
    perPage: parseInt(searchParams.get('per_page') ?? String(DEFAULT_PER_PAGE), 10) || DEFAULT_PER_PAGE,
    sort: searchParams.get('sort') ?? undefined,
    search: searchParams.get('q') ?? undefined,
    filters,
  };
}

function serializeState(state: TicketListState, allowedFilterKeys: readonly TicketFilterKey[]): URLSearchParams {
  const params = new URLSearchParams();
  params.set('page', String(state.page));
  params.set('per_page', String(state.perPage));
  if (state.sort) params.set('sort', state.sort);
  if (state.search) params.set('q', state.search);
  for (const key of allowedFilterKeys) {
    const value = state.filters[key];
    if (value) params.set(key, value);
  }
  return params;
}

/** Builds the `filter` query object the backend's CollectionQuery expects: filter[field][op]=value. */
export function buildTicketQueryParams(state: TicketListState, allowedFilterKeys: readonly TicketFilterKey[]) {
  const filter: Record<string, unknown> = {};
  if (state.search) filter.q = state.search;
  for (const key of allowedFilterKeys) {
    const value = state.filters[key];
    if (value) filter[key] = { eq: value };
  }

  return {
    page: state.page,
    per_page: state.perPage,
    sort: state.sort,
    filter: Object.keys(filter).length > 0 ? filter : undefined,
  };
}

export function useTicketListQuery(mode: TicketQueueMode = 'all', departmentId?: string) {
  const allowedFilterKeys = TICKET_FILTER_KEYS_BY_MODE[mode];
  const [searchParams, setSearchParams] = useSearchParams();
  const state = useMemo(
    () => parseState(searchParams, allowedFilterKeys),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [searchParams, mode]
  );

  const setState = (updates: Partial<TicketListState>) => {
    const next: TicketListState = {
      ...state,
      ...updates,
      filters: updates.filters ? { ...state.filters, ...updates.filters } : state.filters,
    };

    // Changing anything other than the page itself resets pagination to 1.
    if (updates.page === undefined) {
      next.page = 1;
    }

    setSearchParams(serializeState(next, allowedFilterKeys));
  };

  const setFilter = (key: TicketFilterKey, value: string | undefined) => {
    const filters = { ...state.filters };
    if (value) {
      filters[key] = value;
    } else {
      delete filters[key];
    }
    setState({ filters, page: 1 });
  };

  const clearFilters = () => {
    setSearchParams(serializeState({ ...state, filters: {}, search: undefined, page: 1 }, allowedFilterKeys));
  };

  const applyState = (next: Omit<TicketListState, 'page'>) => {
    setSearchParams(serializeState({ ...next, page: 1 }, allowedFilterKeys));
  };

  const params = useMemo(
    () => buildTicketQueryParams(state, allowedFilterKeys),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [state, mode]
  );

  // All three are called unconditionally (stable hook order); `enabled` picks
  // which one actually fetches for the current mode.
  const allQuery = useGetTickets(params, {
    query: { placeholderData: keepPreviousData, enabled: mode === 'all' },
  });
  const mineQuery = useGetTicketQueueMine(params, {
    query: { placeholderData: keepPreviousData, enabled: mode === 'mine' },
  });
  const departmentQuery = useGetTicketQueueDepartment(departmentId ?? '', params, {
    query: { placeholderData: keepPreviousData, enabled: mode === 'department' && !!departmentId },
  });

  const query = (mode === 'all' ? allQuery : mode === 'mine' ? mineQuery : departmentQuery) as unknown as UseQueryResult<
    ApiPage<TicketListRow>,
    unknown
  >;

  return {
    state,
    setState,
    setFilter,
    clearFilters,
    applyState,
    allowedFilterKeys,
    query,
  };
}
