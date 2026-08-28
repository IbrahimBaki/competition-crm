import { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { keepPreviousData, type UseQueryResult } from '@tanstack/react-query';
import { useGetCustomers } from '@/api/generated/customers/customers';
import type { ApiPage } from '@/api/http/envelope';
import type { CustomerListRow, CustomerStatus } from '../types';
import { toCustomerListRow } from '../api/wire';

// Allowed sort/filter fields mirror CustomerController::index() exactly
// (CollectionQuerySpec). A filter or sort field outside these sets is
// rejected by the backend with a 422 — see .squad/gaps/34-481.md #5.
// Notably there is NO tier/company-account filter support server-side, so
// none is offered here.
export const CUSTOMER_SORT_FIELDS = ['name', 'status', 'created_at'] as const;
export type CustomerSortField = (typeof CUSTOMER_SORT_FIELDS)[number];

const SEARCH_DEBOUNCE_MS = 300;
const DEFAULT_PER_PAGE = 25;

export interface CustomerListState {
  page: number;
  perPage: number;
  sort?: string; // e.g. "-created_at"
  status?: CustomerStatus;
  search?: string;
}

function parseState(searchParams: URLSearchParams): CustomerListState {
  return {
    page: Math.max(1, parseInt(searchParams.get('page') ?? '1', 10) || 1),
    perPage: parseInt(searchParams.get('per_page') ?? String(DEFAULT_PER_PAGE), 10) || DEFAULT_PER_PAGE,
    sort: searchParams.get('sort') ?? undefined,
    status: (searchParams.get('status') as CustomerStatus | null) ?? undefined,
    search: searchParams.get('q') ?? undefined,
  };
}

function serializeState(state: CustomerListState): URLSearchParams {
  const params = new URLSearchParams();
  params.set('page', String(state.page));
  params.set('per_page', String(state.perPage));
  if (state.sort) params.set('sort', state.sort);
  if (state.status) params.set('status', state.status);
  if (state.search) params.set('q', state.search);
  return params;
}

/** Builds the `filter`/`sort` query object CustomerController::index() expects. */
export function buildCustomerQueryParams(state: CustomerListState) {
  const filter: Record<string, unknown> = {};
  // Empty/whitespace-only search drops the `q` param entirely rather than
  // sending `q=` — the list falls back to the unfiltered first page.
  const trimmedSearch = state.search?.trim();
  if (trimmedSearch) filter.q = trimmedSearch;
  if (state.status) filter.status = { eq: state.status };

  return {
    page: state.page,
    per_page: state.perPage,
    sort: state.sort,
    filter: Object.keys(filter).length > 0 ? filter : undefined,
  };
}

export function useCustomerListQuery() {
  const [searchParams, setSearchParams] = useSearchParams();
  const state = useMemo(() => parseState(searchParams), [searchParams]);

  // The search box is debounced: typing updates `searchDraft` immediately
  // (for a responsive input) but the URL — and therefore the request — only
  // updates SEARCH_DEBOUNCE_MS after the last keystroke, so a shared link
  // reproduces the settled result set rather than an intermediate keystroke.
  const [searchDraft, setSearchDraft] = useState(state.search ?? '');

  useEffect(() => {
    setSearchDraft(state.search ?? '');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [state.search]);

  const setState = (updates: Partial<CustomerListState>) => {
    const next: CustomerListState = { ...state, ...updates };
    if (updates.page === undefined) {
      next.page = 1;
    }
    setSearchParams(serializeState(next));
  };

  // Arabic-variant matching (e.g. "احمد" matching "أحمد") happens server-side
  // in ArabicTextNormaliser — the raw query string is sent verbatim, never
  // normalised/transliterated/lowercased client-side.
  const setSearch = (value: string) => {
    setSearchDraft(value);
  };

  useEffect(() => {
    const handle = setTimeout(() => {
      const trimmed = searchDraft.trim();
      if (trimmed !== (state.search ?? '')) {
        setState({ search: trimmed || undefined });
      }
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, SEARCH_DEBOUNCE_MS);
    return () => clearTimeout(handle);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchDraft]);

  const setStatus = (status: CustomerStatus | undefined) => setState({ status });
  const setSort = (sort: string | undefined) => setState({ sort });

  const resetFilters = () => {
    setSearchDraft('');
    setSearchParams(serializeState({ page: 1, perPage: state.perPage }));
  };

  const params = useMemo(() => buildCustomerQueryParams(state), [state]);

  const rawQuery = useGetCustomers(params, {
    query: { placeholderData: keepPreviousData },
  }) as unknown as UseQueryResult<ApiPage<Record<string, unknown>>, unknown>;

  const query = useMemo(() => {
    if (!rawQuery.data) return rawQuery as unknown as UseQueryResult<ApiPage<CustomerListRow>, unknown>;
    return {
      ...rawQuery,
      data: { items: rawQuery.data.items.map(toCustomerListRow), meta: rawQuery.data.meta },
    } as unknown as UseQueryResult<ApiPage<CustomerListRow>, unknown>;
  }, [rawQuery]);

  return {
    state,
    searchDraft,
    setSearch,
    setStatus,
    setSort,
    setState,
    resetFilters,
    query,
  };
}
