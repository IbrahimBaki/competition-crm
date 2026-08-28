import { describe, it, expect, vi } from 'vitest';
import type { ReactNode } from 'react';
import { createElement } from 'react';
import { renderHook, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useSlaRiskTickets } from '../useSlaRiskTickets';

let mockData: unknown;

vi.mock('@/api/generated/ticketing/ticketing', () => ({
  useGetTicketQueueMine: () => ({ data: mockData, isLoading: false, isError: false }),
}));

function wrapper({ children }: { children: ReactNode }) {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return createElement(QueryClientProvider, { client: queryClient }, children);
}

describe('useSlaRiskTickets', () => {
  it('reports unavailable when queue rows carry no sla field (the real, verified backend shape)', async () => {
    mockData = { items: [{ uuid: 't-1', reference: 'TCK-1', subject: 'x', status: 'open' }], meta: { total: 1 } };

    const { result } = renderHook(() => useSlaRiskTickets(), { wrapper });

    await waitFor(() => expect(result.current.available).toBe(false));
    expect(result.current.items).toEqual([]);
  });

  it('derives risk rows only from a server-provided sla field when present, never via Date arithmetic', async () => {
    mockData = {
      items: [
        { uuid: 't-1', reference: 'TCK-1', subject: 'x', sla: { first_response: { state: 'breached' } } },
        { uuid: 't-2', reference: 'TCK-2', subject: 'y', sla: { first_response: null, resolution: null } },
      ],
      meta: { total: 2 },
    };

    const { result } = renderHook(() => useSlaRiskTickets(), { wrapper });

    await waitFor(() => expect(result.current.available).toBe(true));
    expect(result.current.items).toHaveLength(1);
    expect((result.current.items[0] as { uuid: string }).uuid).toBe('t-1');
  });
});
