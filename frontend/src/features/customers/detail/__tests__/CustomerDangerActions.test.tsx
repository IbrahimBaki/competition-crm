import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { screen, cleanup, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders } from '../../../tickets/__tests__/test-utils';
import { CustomerDangerActions } from '../CustomerDangerActions';
import type { CustomerDetail } from '../../types';

const postCustomerBlock = vi.fn();
const postCustomerUnblock = vi.fn();

vi.mock('@/api/generated/customers/customers', async () => {
  const actual = await vi.importActual<typeof import('@/api/generated/customers/customers')>(
    '@/api/generated/customers/customers'
  );
  return {
    ...actual,
    postCustomerBlock: (...args: unknown[]) => postCustomerBlock(...args),
    postCustomerUnblock: (...args: unknown[]) => postCustomerUnblock(...args),
  };
});

const activeCustomer: CustomerDetail = {
  uuid: 'c-1',
  name: 'Ada Lovelace',
  preferredLocale: 'en',
  status: 'active',
  blockedReason: null,
  blockedAt: null,
  companyAccount: null,
  contacts: [],
  createdAt: null,
  updatedAt: null,
};

describe('CustomerDangerActions', () => {
  beforeEach(() => {
    postCustomerBlock.mockReset();
    postCustomerBlock.mockResolvedValue({ uuid: 'c-1', name: 'Ada Lovelace', status: 'blocked', contacts: [] });
    postCustomerUnblock.mockReset();
    postCustomerUnblock.mockResolvedValue({ uuid: 'c-1', name: 'Ada Lovelace', status: 'active', contacts: [] });
  });

  afterEach(() => cleanup());

  it('the block button is absent (not disabled) without customers.block permission', () => {
    renderWithProviders(<CustomerDangerActions customer={activeCustomer} />, { permissions: [] });
    expect(screen.queryByRole('button', { name: /block customer/i })).not.toBeInTheDocument();
  });

  it('block requires a reason and states the consequence before confirming', async () => {
    const user = userEvent.setup();
    renderWithProviders(<CustomerDangerActions customer={activeCustomer} />, { permissions: ['customers.block'] });

    await user.click(screen.getByRole('button', { name: /block customer/i }));

    expect(screen.getByText(/no longer.*raise tickets/i)).toBeInTheDocument();
    const buttons = screen.getAllByRole('button', { name: /block customer/i });
    const confirmButton = buttons[1];
    if (!confirmButton) throw new Error('confirm button not found');
    expect(confirmButton).toBeDisabled();

    await user.type(screen.getByLabelText(/reason/i), 'Repeated abuse');
    expect(confirmButton).not.toBeDisabled();

    await user.click(confirmButton);
    await waitFor(() => expect(postCustomerBlock).toHaveBeenCalledTimes(1));
    const [, body] = postCustomerBlock.mock.calls[0];
    expect(body).toMatchObject({ reason: 'Repeated abuse' });
  });

  it('unblock states that intake resumes immediately', async () => {
    const blocked: CustomerDetail = { ...activeCustomer, status: 'blocked' };
    const user = userEvent.setup();
    renderWithProviders(<CustomerDangerActions customer={blocked} />, { permissions: ['customers.block'] });

    await user.click(screen.getByRole('button', { name: /unblock customer/i }));
    expect(screen.getByText(/resumes immediately|raise tickets again immediately/i)).toBeInTheDocument();
  });

  it('never renders an anonymise action — no backend endpoint exists for it (see .squad/gaps/34-481.md #4)', () => {
    renderWithProviders(<CustomerDangerActions customer={activeCustomer} />, {
      permissions: ['customers.block', 'customers.merge', 'customers.update'],
    });
    expect(screen.queryByRole('button', { name: /anonymise/i })).not.toBeInTheDocument();
  });

  it('renders nothing for an anonymised customer', () => {
    const anonymised: CustomerDetail = { ...activeCustomer, status: 'anonymised' };
    const { container } = renderWithProviders(<CustomerDangerActions customer={anonymised} />, {
      permissions: ['customers.block'],
    });
    expect(container).toBeEmptyDOMElement();
  });
});
