import { describe, it, expect } from 'vitest';
import type { ReactNode } from 'react';
import { createElement } from 'react';
import { renderHook, act } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { vi } from 'vitest';
import { useBranchListQuery, buildBranchQueryParams } from '../useBranchListQuery';

const useGetBranches = vi.fn((..._args: unknown[]) => ({ data: undefined, isLoading: true, isError: false }));

vi.mock('@/api/generated/organization/organization', () => ({
  useGetBranches: (...args: unknown[]) => (useGetBranches as (...args: unknown[]) => unknown)(...args),
}));

function wrapper({ children }: { children: ReactNode }) {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return createElement(
    QueryClientProvider,
    { client: queryClient },
    createElement(MemoryRouter, { initialEntries: ['/admin/branches'] }, children)
  );
}

describe('useBranchListQuery', () => {
  it('serializes state to the page/per_page/sort params the generated client expects', () => {
    const params = buildBranchQueryParams({ page: 2, perPage: 25, sort: '-created_at' });
    expect(params).toEqual({ page: 2, per_page: 25, sort: '-created_at' });
  });

  it('threads pagination and sort state into useGetBranches params', () => {
    const { result } = renderHook(() => useBranchListQuery(), { wrapper });

    act(() => {
      result.current.setState({ page: 3 });
    });
    expect(result.current.state.page).toBe(3);

    act(() => {
      result.current.setSort('-created_at');
    });
    expect(result.current.state.sort).toBe('-created_at');
    // setSort/setState without an explicit page resets pagination to 1.
    expect(result.current.state.page).toBe(1);

    const lastCallParams = useGetBranches.mock.calls[useGetBranches.mock.calls.length - 1]?.[0];
    expect(lastCallParams).toEqual({ page: 1, per_page: 25, sort: '-created_at' });
  });
});
