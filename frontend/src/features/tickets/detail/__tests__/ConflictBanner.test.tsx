import { describe, it, expect, vi } from 'vitest';
import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders } from '../../__tests__/test-utils';
import { ConflictBanner } from '../ConflictBanner';
import type { NormalisedApiError } from '@/api/http/errors';

const conflictError: NormalisedApiError = {
  status: 409,
  code: 'ticket.version_conflict',
  message: 'Ticket has been modified; please refresh and try again',
  fieldErrors: {},
  requestId: 'req-1',
  kind: 'conflict',
};

describe('ConflictBanner', () => {
  it('renders the server message and triggers a reload without reporting silent success', async () => {
    const onReload = vi.fn();
    const user = userEvent.setup();

    renderWithProviders(<ConflictBanner error={conflictError} onReload={onReload} />);

    expect(screen.getByRole('alert')).toHaveTextContent(conflictError.message);

    await user.click(screen.getByRole('button', { name: /reload latest/i }));
    expect(onReload).toHaveBeenCalledTimes(1);
  });
});
