import { describe, it, expect, vi } from 'vitest';
import type { ReactNode } from 'react';
import { createElement } from 'react';
import { renderHook, act, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useCustomerListQuery, buildCustomerQueryParams } from '../useCustomerListQuery';

const useGetCustomers = vi.fn(() => ({ data: undefined, isLoading: true, isError: false }));

vi.mock('@/api/generated/customers/customers', () => ({
  useGetCustomers: (...args: unknown[]) => (useGetCustomers as (...args: unknown[]) => unknown)(...args),
}));

function wrapper({ children }: { children: ReactNode }) {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return createElement(
    QueryClientProvider,
    { client: queryClient },
    createElement(MemoryRouter, { initialEntries: ['/customers'] }, children)
  );
}

describe('useCustomerListQuery', () => {
  it('serializes state to the CollectionQuery parameter shape (status filter only — no tier/company)', () => {
    const params = buildCustomerQueryParams({ page: 2, perPage: 25, sort: '-created_at', search: 'billing', status: 'blocked' });

    expect(params).toEqual({
      page: 2,
      per_page: 25,
      sort: '-created_at',
      filter: { q: 'billing', status: { eq: 'blocked' } },
    });
  });

  it('drops the q param entirely for an empty/whitespace-only search', () => {
    const params = buildCustomerQueryParams({ page: 1, perPage: 25, search: '   ' });
    expect(params.filter).toBeUndefined();
  });

  it('round-trips status and page through the URL search params', () => {
    const { result } = renderHook(() => useCustomerListQuery(), { wrapper });

    act(() => {
      result.current.setStatus('blocked');
    });
    expect(result.current.state.status).toBe('blocked');
    expect(result.current.state.page).toBe(1);

    act(() => {
      result.current.setState({ page: 3 });
    });
    expect(result.current.state.page).toBe(3);
    expect(result.current.state.status).toBe('blocked');
  });

  it('debounces the search input: a burst of keystrokes results in a single settled URL update', async () => {
    vi.useFakeTimers({ shouldAdvanceTime: true });
    const { result } = renderHook(() => useCustomerListQuery(), { wrapper });

    act(() => {
      result.current.setSearch('a');
      result.current.setSearch('ah');
      result.current.setSearch('ahm');
      result.current.setSearch('ahmed');
    });

    // Before the debounce window elapses, the committed URL state is untouched.
    expect(result.current.state.search).toBeUndefined();

    act(() => {
      vi.advanceTimersByTime(300);
    });

    await waitFor(() => expect(result.current.state.search).toBe('ahmed'));
    vi.useRealTimers();
  });

  it('sends an Arabic query verbatim — no normalisation, no transliteration', async () => {
    vi.useFakeTimers({ shouldAdvanceTime: true });
    const { result } = renderHook(() => useCustomerListQuery(), { wrapper });

    const arabicName = 'أحمد';
    act(() => {
      result.current.setSearch(arabicName);
    });
    act(() => {
      vi.advanceTimersByTime(300);
    });

    await waitFor(() => expect(result.current.state.search).toBe(arabicName));

    const params = buildCustomerQueryParams(result.current.state);
    expect(params.filter).toEqual({ q: arabicName });
    vi.useRealTimers();
  });
});
