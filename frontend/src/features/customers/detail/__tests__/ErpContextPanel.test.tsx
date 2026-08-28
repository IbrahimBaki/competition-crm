import { describe, it, expect, vi, afterEach } from 'vitest';
import { AxiosError } from 'axios';
import { screen, cleanup, waitFor } from '@testing-library/react';
import { renderWithProviders } from '../../../tickets/__tests__/test-utils';
import { ErpContextPanel } from '../ErpContextPanel';

const apiRequest = vi.fn();

vi.mock('@/api/http/mutator', () => ({
  apiRequest: (...args: unknown[]) => apiRequest(...args),
}));

function httpError(status: number, message = 'error') {
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

function ProfileWithErp() {
  return (
    <div>
      <p>Rest of the profile</p>
      <ErpContextPanel customerUuid="c-1" />
    </div>
  );
}

describe('ErpContextPanel', () => {
  afterEach(() => {
    cleanup();
    apiRequest.mockReset();
  });

  it('renders ERP data on success', async () => {
    apiRequest.mockResolvedValue({
      customer_id: 'c-1',
      erp_context: {
        legal_name: 'Acme LLC',
        account_status: 'active',
        credit_hold: false,
        outstanding_balance: 0,
        service_tier: 'gold',
        contract_end_date: '2027-01-01',
      },
    });

    renderWithProviders(<ProfileWithErp />);

    await waitFor(() => expect(screen.getByText('Acme LLC')).toBeInTheDocument());
    expect(screen.getByText('Rest of the profile')).toBeInTheDocument();
  });

  it('a 500 renders the inline retry notice and the surrounding profile still renders', async () => {
    apiRequest.mockRejectedValue(httpError(500));

    renderWithProviders(<ProfileWithErp />);

    await waitFor(() => expect(screen.getByText(/erp data unavailable/i)).toBeInTheDocument());
    expect(screen.getByRole('button', { name: /retry/i })).toBeInTheDocument();
    expect(screen.getByText('Rest of the profile')).toBeInTheDocument();
  });

  it('a 403 renders nothing at all', async () => {
    apiRequest.mockRejectedValue(httpError(403));

    const { container } = renderWithProviders(<ProfileWithErp />);

    // No ERP heading, no error text — the panel renders null once settled.
    await waitFor(() => expect(container.querySelectorAll('section')).toHaveLength(0));
    expect(screen.queryByText(/erp/i)).not.toBeInTheDocument();
    expect(screen.getByText('Rest of the profile')).toBeInTheDocument();
  });

  it('a 404 renders the empty state ("no ERP record linked")', async () => {
    apiRequest.mockRejectedValue(httpError(404));

    renderWithProviders(<ProfileWithErp />);

    await waitFor(() => expect(screen.getByText(/no erp record linked/i)).toBeInTheDocument());
    expect(screen.getByText('Rest of the profile')).toBeInTheDocument();
  });
});
