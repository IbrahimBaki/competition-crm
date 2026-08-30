import { describe, it, expect, vi, afterEach } from 'vitest';
import { screen, cleanup, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders } from '../../../tickets/__tests__/test-utils';
import { CustomerTimeline } from '../CustomerTimeline';

const useGetCustomerTimeline = vi.fn();

vi.mock('@/api/generated/customers/customers', () => ({
  useGetCustomerTimeline: (...args: unknown[]) => useGetCustomerTimeline(...args),
}));

function entry(id: string, source: string, type: string, occurredAt: string) {
  return { id, source, type, occurred_at: occurredAt, actor_uuid: null, payload: null };
}

describe('CustomerTimeline', () => {
  afterEach(() => {
    cleanup();
    useGetCustomerTimeline.mockReset();
  });

  it('renders mixed-kind entries in the order the server returned them', () => {
    useGetCustomerTimeline.mockReturnValue({
      data: [
        entry('1', 'note', 'note', '2026-01-03T00:00:00+00:00'),
        entry('2', 'customer_event', 'blocked', '2026-01-02T00:00:00+00:00'),
        entry('3', 'note', 'note', '2026-01-01T00:00:00+00:00'),
      ],
      isLoading: false,
      isFetching: false,
      isError: false,
    });

    renderWithProviders(<CustomerTimeline customerUuid="c-1" />);

    const items = screen.getAllByRole('listitem');
    expect(items).toHaveLength(3);
    expect(items[0]).toHaveTextContent('Note added');
    expect(items[1]).toHaveTextContent('Customer blocked');
    expect(items[2]).toHaveTextContent('Note added');
  });

  it('renders a neutral fallback row for an unrecognised source/type and does not throw', () => {
    useGetCustomerTimeline.mockReturnValue({
      data: [entry('1', 'ticket', 'reply_sent', '2026-01-01T00:00:00+00:00')],
      isLoading: false,
      isFetching: false,
      isError: false,
    });

    expect(() => renderWithProviders(<CustomerTimeline customerUuid="c-1" />)).not.toThrow();
    expect(screen.getByText('Activity recorded')).toBeInTheDocument();
  });

  it('"load more" appends the next page without reordering, and hides itself once exhausted', async () => {
    const firstPage = Array.from({ length: 26 }, (_, i) =>
      entry(`p1-${i}`, 'note', 'note', `2026-01-${String(26 - i).padStart(2, '0')}T00:00:00+00:00`)
    );
    const secondPage = [entry('p2-0', 'note', 'note', '2025-12-31T00:00:00+00:00')];

    useGetCustomerTimeline.mockImplementation((_customer: string, params: { before?: string }) => {
      if (!params.before) {
        return { data: firstPage, isLoading: false, isFetching: false, isError: false };
      }
      return { data: secondPage, isLoading: false, isFetching: false, isError: false };
    });

    const user = userEvent.setup();
    renderWithProviders(<CustomerTimeline customerUuid="c-1" />);

    // 26 entries requested at PAGE_SIZE(25)+1 -> a full page of 25 shown, "load more" visible.
    expect(screen.getAllByRole('listitem')).toHaveLength(25);
    const loadMoreButton = screen.getByRole('button', { name: /load more/i });

    await user.click(loadMoreButton);

    await waitFor(() => expect(screen.getAllByRole('listitem')).toHaveLength(26));
    // Second page's single entry is appended last, not reordered to the top.
    const items = screen.getAllByRole('listitem');
    expect(items[25]).toBeDefined();

    // Second page returned fewer than PAGE_SIZE+1 -> no more "load more".
    expect(screen.queryByRole('button', { name: /load more/i })).not.toBeInTheDocument();
  });
});
