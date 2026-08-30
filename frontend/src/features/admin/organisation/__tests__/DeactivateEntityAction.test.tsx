import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { screen, within, cleanup, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { AxiosError } from 'axios';
import { renderWithProviders } from '../../../tickets/__tests__/test-utils';
import { DeactivateEntityAction } from '../DeactivateEntityAction';

const postBranchActivate = vi.fn();
const postBranchDeactivate = vi.fn();

vi.mock('@/api/generated/organization/organization', async () => {
  const actual = await vi.importActual<typeof import('@/api/generated/organization/organization')>(
    '@/api/generated/organization/organization'
  );
  return {
    ...actual,
    postBranchActivate: (...args: unknown[]) => postBranchActivate(...args),
    postBranchDeactivate: (...args: unknown[]) => postBranchDeactivate(...args),
  };
});

describe('DeactivateEntityAction', () => {
  beforeEach(() => {
    postBranchActivate.mockReset();
    postBranchDeactivate.mockReset();
    postBranchDeactivate.mockResolvedValue(undefined);
  });

  afterEach(() => cleanup());

  it('clicking deactivate opens the dialog and renders the consequence sentence', async () => {
    const user = userEvent.setup();
    renderWithProviders(<DeactivateEntityAction entityType="branch" id="b-1" isActive />);

    await user.click(screen.getByRole('button', { name: /deactivate/i }));

    expect(screen.getByRole('dialog')).toBeInTheDocument();
    expect(screen.getByText(/departments/i)).toBeInTheDocument();
  });

  it('a 409 branch-has-active-departments response keeps the dialog open and shows the translated message', async () => {
    const user = userEvent.setup();
    postBranchDeactivate.mockRejectedValueOnce(
      new AxiosError('Conflict', undefined, undefined, undefined, {
        status: 409,
        data: { error: { code: 'branch.has_active_departments', message: 'Conflict' } },
      } as never)
    );

    renderWithProviders(<DeactivateEntityAction entityType="branch" id="b-1" isActive />);

    await user.click(screen.getByRole('button', { name: /^deactivate$/i }));
    const dialog = screen.getByRole('dialog');
    await user.click(within(dialog).getByRole('button', { name: /deactivate/i }));

    await waitFor(() => expect(postBranchDeactivate).toHaveBeenCalledTimes(1));
    expect(await screen.findByRole('dialog')).toBeInTheDocument();
  });

  it('a successful deactivation closes the dialog', async () => {
    const user = userEvent.setup();
    renderWithProviders(<DeactivateEntityAction entityType="branch" id="b-1" isActive />);

    await user.click(screen.getByRole('button', { name: /^deactivate$/i }));
    const dialog = screen.getByRole('dialog');
    const confirmButton = within(dialog).getByRole('button', { name: /deactivate/i });
    await user.click(confirmButton);

    await waitFor(() => expect(postBranchDeactivate).toHaveBeenCalledTimes(1));
    await waitFor(() => expect(screen.queryByRole('dialog')).not.toBeInTheDocument());
  });

  it('renders an activate button (no dialog) when the entity is inactive', () => {
    renderWithProviders(<DeactivateEntityAction entityType="branch" id="b-1" isActive={false} />);
    expect(screen.getByRole('button', { name: /activate/i })).toBeInTheDocument();
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
  });
});
