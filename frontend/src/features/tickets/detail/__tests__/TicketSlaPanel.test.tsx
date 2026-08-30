import { describe, it, expect, afterEach, vi } from 'vitest';
import { screen, cleanup } from '@testing-library/react';
import { renderWithProviders } from '../../__tests__/test-utils';
import { TicketSlaPanel } from '../TicketSlaPanel';
import type { TicketSlaBlock } from '../../types';

const sla: TicketSlaBlock = {
  first_response: {
    target_type: 'first_response',
    state: 'running',
    due_at: '2026-01-01T12:00:00Z',
    target_minutes: 60,
    elapsed_minutes: 42,
    remaining_minutes: 18,
    warning_fired: true,
    paused_at: null,
  },
  resolution: null,
};

describe('TicketSlaPanel', () => {
  afterEach(() => {
    vi.useRealTimers();
    cleanup();
  });

  it('renders the figures exactly as given in the payload', () => {
    renderWithProviders(<TicketSlaPanel sla={sla} />);

    expect(screen.getByText('60 minutes')).toBeInTheDocument();
    expect(screen.getByText('42 minutes')).toBeInTheDocument();
    expect(screen.getByText('18 minutes')).toBeInTheDocument();
    expect(screen.getByText(/approaching breach/i)).toBeInTheDocument();
  });

  it('does not change the rendered figures when the system clock changes', () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date('2020-01-01T00:00:00Z'));
    const { unmount } = renderWithProviders(<TicketSlaPanel sla={sla} />);
    const before = screen.getByText('18 minutes').textContent;
    unmount();

    vi.setSystemTime(new Date('2030-06-15T00:00:00Z'));
    renderWithProviders(<TicketSlaPanel sla={sla} />);
    const after = screen.getByText('18 minutes').textContent;

    expect(after).toBe(before);
  });
});
