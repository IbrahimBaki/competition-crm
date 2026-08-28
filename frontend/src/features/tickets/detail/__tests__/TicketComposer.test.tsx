import { describe, it, expect, vi, beforeEach } from 'vitest';
import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders, ALL_TICKET_PERMISSIONS } from '../../__tests__/test-utils';
import { TicketComposer } from '../TicketComposer';
import type { TicketDetail } from '../../types';

const postTicketMessages = vi.fn();

vi.mock('@/api/generated/ticketing/ticketing', async () => {
  const actual = await vi.importActual<typeof import('@/api/generated/ticketing/ticketing')>(
    '@/api/generated/ticketing/ticketing'
  );
  return {
    ...actual,
    postTicketMessages: (...args: unknown[]) => postTicketMessages(...args),
    useGetTicketMessages: () => ({
      data: { items: [{ channel: 'email' }] },
      isLoading: false,
      isError: false,
    }),
  };
});

vi.mock('@/api/generated/customers/customers', () => ({
  useGetCustomer: () => ({ data: { name: 'Ada Lovelace' }, isLoading: false, isError: false }),
}));

const baseTicket: TicketDetail = {
  id: 'ticket-uuid',
  reference: 'TCK-1',
  customer_id: 'customer-uuid',
  department_id: null,
  category_id: null,
  assignee_id: null,
  subject: 'Test subject',
  body: 'Test body',
  status: { uuid: 's1', key: 'open', name: 'Open', lifecycle_type: 'open', stops_sla_clock: false },
  priority: 'normal',
  custom_fields: null,
  available_transitions: [],
  merged_into_id: null,
  parent_ticket_id: null,
  version: 1,
  assigned_at: null,
  reopen_deadline_at: null,
  reopened_count: 0,
  is_watched: false,
  created_at: null,
  updated_at: null,
  sla: null,
};

describe('TicketComposer', () => {
  beforeEach(() => {
    postTicketMessages.mockReset();
    postTicketMessages.mockResolvedValue({ uuid: 'msg-1' });
  });

  it('disables submit until a mode is chosen', () => {
    renderWithProviders(<TicketComposer ticket={baseTicket} />, { permissions: ALL_TICKET_PERMISSIONS });

    expect(screen.getByRole('button', { name: /send reply/i })).toBeDisabled();
    expect(screen.getByText(/choose public reply or internal note/i)).toBeInTheDocument();
  });

  it('selecting internal mode shows the internal warning and sends is_internal: true', async () => {
    const user = userEvent.setup();
    renderWithProviders(<TicketComposer ticket={baseTicket} />, { permissions: ALL_TICKET_PERMISSIONS });

    await user.click(screen.getByRole('radio', { name: /internal note/i }));
    expect(screen.getByText(/customer will not see this/i)).toBeInTheDocument();

    await user.type(screen.getByPlaceholderText(/type your message/i), 'This is internal');
    await user.click(screen.getByRole('button', { name: /add internal note/i }));

    await waitFor(() => expect(postTicketMessages).toHaveBeenCalledTimes(1));
    const [, body] = postTicketMessages.mock.calls[0];
    expect(body).toMatchObject({ is_internal: true, channel: 'internal', body: 'This is internal' });
  });

  it('selecting public mode sends is_internal: false with the reply channel', async () => {
    const user = userEvent.setup();
    renderWithProviders(<TicketComposer ticket={baseTicket} />, { permissions: ALL_TICKET_PERMISSIONS });

    await user.click(screen.getByRole('radio', { name: /public reply/i }));
    await user.type(screen.getByPlaceholderText(/type your message/i), 'Hello customer');
    await user.click(screen.getByRole('button', { name: /send reply/i }));

    await waitFor(() => expect(postTicketMessages).toHaveBeenCalledTimes(1));
    const [, body] = postTicketMessages.mock.calls[0];
    expect(body).toMatchObject({ is_internal: false, channel: 'email', body: 'Hello customer' });
  });

  it('preserves the draft text when the send fails', async () => {
    postTicketMessages.mockRejectedValueOnce({
      status: 422,
      code: 'validation_failed',
      message: 'Validation failed',
      fieldErrors: {},
      requestId: null,
      kind: 'validation',
    });

    const user = userEvent.setup();
    renderWithProviders(<TicketComposer ticket={baseTicket} />, { permissions: ALL_TICKET_PERMISSIONS });

    await user.click(screen.getByRole('radio', { name: /public reply/i }));
    const textarea = screen.getByPlaceholderText(/type your message/i);
    await user.type(textarea, 'Draft that fails');
    await user.click(screen.getByRole('button', { name: /send reply/i }));

    await waitFor(() => expect(postTicketMessages).toHaveBeenCalledTimes(1));
    await waitFor(() => expect(textarea).toHaveValue('Draft that fails'));
  });
});
