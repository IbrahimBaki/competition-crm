import { useSearchParams } from 'react-router-dom';

export interface PaginationState {
  page: number;
  perPage: number;
  sort?: string;
  filter: Record<string, string | string[]>;
  search?: string;
}

export interface CollectionQueryParams {
  page?: number;
  per_page?: number;
  sort?: string;
  'filter[q]'?: string;
  [key: `filter[${string}]`]: string | string[] | undefined;
}

export function buildCollectionParams(state: PaginationState): CollectionQueryParams {
  const params: CollectionQueryParams = {
    page: state.page,
    per_page: state.perPage,
  };

  if (state.sort) {
    params.sort = state.sort;
  }

  if (state.search) {
    params['filter[q]'] = state.search;
  }

  Object.entries(state.filter).forEach(([key, value]) => {
    params[`filter[${key}]`] = value;
  });

  return params;
}

export function usePaginatedQueryState(defaults: Partial<PaginationState> = {}) {
  const [searchParams, setSearchParams] = useSearchParams();

  const state: PaginationState = {
    page: parseInt(searchParams.get('page') ?? '1', 10),
    perPage: parseInt(searchParams.get('per_page') ?? '25', 10),
    sort: searchParams.get('sort') ?? defaults.sort,
    search: searchParams.get('search') ?? defaults.search,
    filter: {},
  };

  // Extract filters from search params
  searchParams.forEach((value, key) => {
    if (key.startsWith('filter[') && key.endsWith(']')) {
      const filterName = key.slice(7, -1);
      state.filter[filterName] = value;
    }
  });

  const updateState = (updates: Partial<PaginationState>) => {
    const newParams = new URLSearchParams();

    newParams.set('page', String(updates.page ?? state.page));
    newParams.set('per_page', String(updates.perPage ?? state.perPage));

    if (updates.sort ?? state.sort) {
      newParams.set('sort', updates.sort ?? state.sort!);
    }

    if (updates.search ?? state.search) {
      newParams.set('search', updates.search ?? state.search!);
    }

    const filters = { ...state.filter, ...updates.filter };
    Object.entries(filters).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach((v) => newParams.append(`filter[${key}]`, v));
      } else if (value) {
        newParams.set(`filter[${key}]`, value);
      }
    });

    setSearchParams(newParams);
  };

  return { state, updateState };
}
