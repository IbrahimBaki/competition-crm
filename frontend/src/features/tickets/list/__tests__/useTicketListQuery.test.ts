import { describe, it, expect, vi } from 'vitest';
import type { ReactNode } from 'react';
import { createElement } from 'react';
import { renderHook, act } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useTicketListQuery, buildTicketQueryParams, TICKET_FILTER_KEYS } from '../useTicketListQuery';

vi.mock('@/api/generated/ticketing/ticketing', () => ({
  useGetTickets: () => ({ data: undefined, isLoading: true, isError: false }),
  useGetTicketQueueMine: () => ({ data: undefined, isLoading: true, isError: false }),
  useGetTicketQueueDepartment: () => ({ data: undefined, isLoading: true, isError: false }),
}));

function wrapper({ children }: { children: ReactNode }) {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return createElement(
    QueryClientProvider,
    { client: queryClient },
    createElement(MemoryRouter, { initialEntries: ['/tickets'] }, children)
  );
}

describe('useTicketListQuery', () => {
  it('serializes filter/sort/page state to the CollectionQuery parameter shape', () => {
    const params = buildTicketQueryParams(
      { page: 2, perPage: 25, sort: '-created_at', search: 'billing', filters: { status: 'open', priority: 'high' } },
      TICKET_FILTER_KEYS
    );

    expect(params).toEqual({
      page: 2,
      per_page: 25,
      sort: '-created_at',
      filter: { q: 'billing', status: { eq: 'open' }, priority: { eq: 'high' } },
    });
  });

  it('round-trips state through the URL search params', () => {
    const { result } = renderHook(() => useTicketListQuery('all'), { wrapper });

    act(() => {
      result.current.setFilter('status', 'open');
    });

    expect(result.current.state.filters.status).toBe('open');
    expect(result.current.state.page).toBe(1);

    act(() => {
      result.current.setState({ page: 3 });
    });
    expect(result.current.state.page).toBe(3);
    // Filters survive a page-only change.
    expect(result.current.state.filters.status).toBe('open');
  });

  it('resets the page to 1 when a filter changes', () => {
    const { result } = renderHook(() => useTicketListQuery('all'), { wrapper });

    act(() => {
      result.current.setState({ page: 5 });
    });
    expect(result.current.state.page).toBe(5);

    act(() => {
      result.current.setFilter('priority', 'urgent');
    });
    expect(result.current.state.page).toBe(1);
  });
});
