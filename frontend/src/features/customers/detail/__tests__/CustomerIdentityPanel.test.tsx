import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { AxiosError } from 'axios';
import { screen, cleanup, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders } from '../../../tickets/__tests__/test-utils';
import { CustomerIdentityPanel } from '../CustomerIdentityPanel';

const deleteCustomerContact = vi.fn();
const postCustomerContacts = vi.fn();

vi.mock('@/api/generated/customers/customers', async () => {
  const actual = await vi.importActual<typeof import('@/api/generated/customers/customers')>(
    '@/api/generated/customers/customers'
  );
  return {
    ...actual,
    useGetCustomerContacts: () => ({
      data: {
        items: [
          { uuid: 'ct-1', type: 'email', value: 'ada@example.com', label: null, is_primary: true, verified_at: null },
        ],
        meta: { total: 1, page: 1, per_page: 25, total_pages: 1, request_id: 'r' },
      },
      isLoading: false,
      isError: false,
    }),
    deleteCustomerContact: (...args: unknown[]) => deleteCustomerContact(...args),
    postCustomerContacts: (...args: unknown[]) => postCustomerContacts(...args),
  };
});

function httpError(status: number, message: string) {
  const error = new AxiosError(message);
  error.response = {
    status,
    data: { error: { message } },
    statusText: '',
    headers: {},
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    config: {} as any,
  };
  return error;
}

describe('CustomerIdentityPanel', () => {
  beforeEach(() => {
    deleteCustomerContact.mockReset();
    postCustomerContacts.mockReset();
    postCustomerContacts.mockResolvedValue({ uuid: 'ct-2', type: 'email', value: 'x@example.com', is_primary: false });
  });

  afterEach(() => cleanup());

  it('renders the CustomerMustHaveContactException message inline when removing the last contact', async () => {
    deleteCustomerContact.mockRejectedValue(httpError(422, 'A customer must have at least one contact.'));

    const user = userEvent.setup();
    renderWithProviders(<CustomerIdentityPanel customerUuid="c-1" />, {
      permissions: ['customers.view', 'customers.contact.manage'],
    });

    await user.click(screen.getByRole('button', { name: /remove/i }));

    await waitFor(() => expect(screen.getByText(/at least one contact/i)).toBeInTheDocument());
  });

  it('renders the DuplicateContactIdentityException message inline when adding a duplicate identity', async () => {
    postCustomerContacts.mockRejectedValue(httpError(409, 'That contact identity already belongs to another customer.'));

    const user = userEvent.setup();
    renderWithProviders(<CustomerIdentityPanel customerUuid="c-1" />, {
      permissions: ['customers.view', 'customers.contact.manage'],
    });

    await user.type(screen.getByLabelText(/value/i), 'ada@example.com');
    await user.click(screen.getByRole('button', { name: /add contact/i }));

    await waitFor(() => expect(screen.getByText(/already belongs to another customer/i)).toBeInTheDocument());
  });
});
