import { describe, it, expect, vi, afterEach } from 'vitest';
import { screen, cleanup } from '@testing-library/react';
import { renderWithProviders } from '../../../../tickets/__tests__/test-utils';
import { WorkingWeekPreview } from '../WorkingWeekPreview';
import type { BranchWorkingHour } from '../../../types';

const httpClient = vi.fn();
vi.mock('@/api/http/client', () => ({ httpClient: (...args: unknown[]) => httpClient(...args) }));

const WEEKDAYS: BranchWorkingHour[] = Array.from({ length: 7 }, (_, dayOfWeek) => ({
  dayOfWeek,
  isWorking: dayOfWeek >= 1 && dayOfWeek <= 5,
  opensAt: dayOfWeek >= 1 && dayOfWeek <= 5 ? '09:00' : null,
  closesAt: dayOfWeek >= 1 && dayOfWeek <= 5 ? '17:00' : null,
}));

const ALL_CLOSED: BranchWorkingHour[] = Array.from({ length: 7 }, (_, dayOfWeek) => ({
  dayOfWeek,
  isWorking: false,
  opensAt: null,
  closesAt: null,
}));

describe('WorkingWeekPreview', () => {
  afterEach(() => cleanup());

  it('renders purely from props and never calls the network', () => {
    renderWithProviders(<WorkingWeekPreview days={WEEKDAYS} holidays={[]} />);
    expect(screen.getByText(/40/)).toBeInTheDocument();
    expect(httpClient).not.toHaveBeenCalled();
  });

  it('states there are no working hours configured when every day is closed', () => {
    renderWithProviders(<WorkingWeekPreview days={ALL_CLOSED} holidays={[]} />);
    expect(screen.getByText(/no working hours/i)).toBeInTheDocument();
  });

  it('renders no LTR/RTL-specific markup of its own — direction is inherited from the document, not hardcoded', () => {
    const { container } = renderWithProviders(<WorkingWeekPreview days={WEEKDAYS} holidays={[]} />);
    expect(container.querySelector('[dir]')).not.toBeInTheDocument();
  });
});
