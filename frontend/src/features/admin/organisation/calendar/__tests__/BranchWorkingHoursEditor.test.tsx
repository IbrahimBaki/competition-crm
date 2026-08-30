import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { screen, cleanup, waitFor, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders } from '../../../../tickets/__tests__/test-utils';
import { BranchWorkingHoursEditor } from '../BranchWorkingHoursEditor';
import type { BranchWorkingHour } from '../../../types';

const putBranchWorkingHours = vi.fn();

vi.mock('@/api/generated/organization/organization', async () => {
  const actual = await vi.importActual<typeof import('@/api/generated/organization/organization')>(
    '@/api/generated/organization/organization'
  );
  return {
    ...actual,
    putBranchWorkingHours: (...args: unknown[]) => putBranchWorkingHours(...args),
  };
});

const WEEKDAYS: BranchWorkingHour[] = Array.from({ length: 7 }, (_, dayOfWeek) => ({
  dayOfWeek,
  isWorking: dayOfWeek >= 1 && dayOfWeek <= 5,
  opensAt: dayOfWeek >= 1 && dayOfWeek <= 5 ? '09:00' : null,
  closesAt: dayOfWeek >= 1 && dayOfWeek <= 5 ? '17:00' : null,
}));

describe('BranchWorkingHoursEditor', () => {
  beforeEach(() => {
    putBranchWorkingHours.mockReset();
    putBranchWorkingHours.mockResolvedValue(undefined);
  });

  afterEach(() => cleanup());

  it('submitting sends all seven days in one payload (replace semantics)', async () => {
    const user = userEvent.setup();
    renderWithProviders(
      <BranchWorkingHoursEditor branchId="b-1" initialDays={WEEKDAYS} holidays={[]} />
    );

    await user.click(screen.getByRole('button', { name: /save/i }));

    await waitFor(() => expect(putBranchWorkingHours).toHaveBeenCalledTimes(1));
    const [branchArg, bodyArg] = putBranchWorkingHours.mock.calls[0];
    expect(branchArg).toBe('b-1');
    expect(bodyArg.days).toHaveLength(7);
    expect(bodyArg.days[1]).toMatchObject({ day_of_week: 1, is_working: true, opens_at: '09:00', closes_at: '17:00' });
  });

  it('an open-after-close range blocks submit with a field error', async () => {
    renderWithProviders(
      <BranchWorkingHoursEditor branchId="b-1" initialDays={WEEKDAYS} holidays={[]} />
    );

    const timeInputs = screen.getAllByDisplayValue('09:00');
    fireEvent.change(timeInputs[0] as HTMLInputElement, { target: { value: '18:00' } });

    expect(screen.getByRole('button', { name: /save/i })).toBeDisabled();
    expect(putBranchWorkingHours).not.toHaveBeenCalled();
  });
});
