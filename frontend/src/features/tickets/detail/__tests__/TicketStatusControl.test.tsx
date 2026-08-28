import { describe, it, expect, vi, beforeEach } from 'vitest';
import { screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders, ALL_TICKET_PERMISSIONS } from '../../__tests__/test-utils';
import { TicketStatusControl } from '../TicketStatusControl';
import type { TicketDetail } from '../../types';

const postTicketStatus = vi.fn();

vi.mock('@/api/generated/ticketing/ticketing', async () => {
  const actual = await vi.importActual<typeof import('@/api/generated/ticketing/ticketing')>(
    '@/api/generated/ticketing/ticketing'
  );
  return {
    ...actual,
    postTicketStatus: (...args: unknown[]) => postTicketStatus(...args),
  };
});

function ticketWith(transitions: TicketDetail['available_transitions']): TicketDetail {
  return {
    id: 'ticket-uuid',
    reference: 'TCK-1',
    customer_id: null,
    department_id: null,
    category_id: null,
    assignee_id: null,
    subject: 'Subject',
    body: 'Body',
    status: { uuid: 's-open', key: 'open', name: 'Open', lifecycle_type: 'open', stops_sla_clock: false },
    priority: 'normal',
    custom_fields: null,
    available_transitions: transitions,
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
}

describe('TicketStatusControl', () => {
  beforeEach(() => {
    postTicketStatus.mockReset();
    postTicketStatus.mockResolvedValue({});
  });

  it('renders exactly the server-provided available transitions', () => {
    const ticket = ticketWith([
      { uuid: 't-pending', key: 'pending', name: 'Pending', lifecycle_type: 'pending', requires_reason: false },
      { uuid: 't-resolved', key: 'resolved', name: 'Resolved', lifecycle_type: 'resolved', requires_reason: false },
    ]);

    renderWithProviders(<TicketStatusControl ticket={ticket} />, { permissions: ALL_TICKET_PERMISSIONS });

    const select = screen.getByRole('combobox');
    const options = within(select).getAllByRole('option').map((option) => option.textContent);
    expect(options).toEqual(['Choose a status…', 'Pending', 'Resolved']);
  });

  it('disables the control when there are no available transitions', () => {
    renderWithProviders(<TicketStatusControl ticket={ticketWith([])} />, { permissions: ALL_TICKET_PERMISSIONS });

    expect(screen.getByText(/no status changes are available/i)).toBeInTheDocument();
    expect(screen.queryByRole('combobox')).not.toBeInTheDocument();
  });

  it('opens a reason field for a reason-required transition and blocks submit until filled', async () => {
    const user = userEvent.setup();
    const ticket = ticketWith([
      { uuid: 't-spam', key: 'spam', name: 'Spam', lifecycle_type: 'spam', requires_reason: true },
    ]);

    renderWithProviders(<TicketStatusControl ticket={ticket} />, { permissions: ALL_TICKET_PERMISSIONS });

    await user.selectOptions(screen.getByRole('combobox'), 't-spam');
    const applyButton = screen.getByRole('button', { name: /change status/i });
    expect(applyButton).toBeDisabled();

    const reasonBox = screen.getByPlaceholderText(/reason for this change/i);
    await user.type(reasonBox, 'Marking as spam');
    expect(applyButton).not.toBeDisabled();

    await user.click(applyButton);
    await waitFor(() => expect(postTicketStatus).toHaveBeenCalledTimes(1));
    const [, body] = postTicketStatus.mock.calls[0];
    expect(body).toMatchObject({ status: 't-spam', reason: 'Marking as spam' });
  });
});
