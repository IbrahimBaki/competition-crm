import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { AxiosError } from 'axios';
import { screen, cleanup, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders } from '../../../tickets/__tests__/test-utils';
import { MergePreviewDialog } from '../MergePreviewDialog';
import type { DuplicateCandidate } from '../../types';

const postCustomerMerge = vi.fn();
const apiRequest = vi.fn();

vi.mock('@/api/generated/customers/customers', async () => {
  const actual = await vi.importActual<typeof import('@/api/generated/customers/customers')>(
    '@/api/generated/customers/customers'
  );
  return {
    ...actual,
    postCustomerMerge: (...args: unknown[]) => postCustomerMerge(...args),
  };
});

vi.mock('@/api/http/mutator', () => ({
  apiRequest: (...args: unknown[]) => apiRequest(...args),
}));

function page(total: number) {
  return { items: [], meta: { total, page: 1, per_page: 1, total_pages: 1, request_id: 'r' } };
}

const candidate: DuplicateCandidate = {
  uuid: 'd-1',
  customer: { uuid: 'c-1', name: 'Ada Lovelace' },
  duplicateCustomer: { uuid: 'c-2', name: 'Ada L.' },
  status: 'pending',
  rule: 'name_match',
  evidence: {},
  reviewedAt: null,
  createdAt: '2026-01-01T00:00:00+00:00',
};

describe('MergePreviewDialog', () => {
  beforeEach(() => {
    postCustomerMerge.mockReset();
    apiRequest.mockReset();
    apiRequest.mockImplementation(({ url }: { url: string }) => {
      if (url.endsWith('/contacts')) return Promise.resolve(page(2));
      if (url.endsWith('/notes')) return Promise.resolve(page(3));
      if (url.endsWith('/attachments')) return Promise.resolve(page(1));
      if (url.endsWith('/timeline')) return Promise.resolve([{ source: 'customer_event' }, { source: 'note' }]);
      return Promise.reject(new Error(`unexpected url ${url}`));
    });
  });

  afterEach(() => cleanup());

  it('confirm is disabled until a direction is chosen, then shows survivor/absorbed and the four move counts', async () => {
    const user = userEvent.setup();
    renderWithProviders(
      <MergePreviewDialog candidate={candidate} currentCustomerUuid="c-1" onClose={vi.fn()} onMerged={vi.fn()} />,
      { permissions: ['customers.merge'] }
    );

    const confirmButton = screen.getByRole('button', { name: /^merge$/i });
    expect(confirmButton).toBeDisabled();

    await user.click(screen.getByRole('radio', { name: /ada lovelace survives/i }));
    expect(confirmButton).not.toBeDisabled();

    await waitFor(() => expect(screen.getByText(/2 contact/i)).toBeInTheDocument());
    expect(screen.getByText(/3 note/i)).toBeInTheDocument();
    expect(screen.getByText(/1 attachment/i)).toBeInTheDocument();
    expect(screen.getByText(/1 event/i)).toBeInTheDocument();
    expect(screen.getByText(/cannot be undone/i)).toBeInTheDocument();
  });

  it('a 409 response closes the dialog, surfaces the normalised error, and does not throw', async () => {
    const conflict = new AxiosError('conflict');
    conflict.response = {
      status: 409,
      data: { error: { message: 'This customer was already merged.' } },
      statusText: '',
      headers: {},
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      config: {} as any,
    };
    postCustomerMerge.mockRejectedValue(conflict);

    const onMerged = vi.fn();
    const user = userEvent.setup();
    renderWithProviders(
      <MergePreviewDialog candidate={candidate} currentCustomerUuid="c-1" onClose={vi.fn()} onMerged={onMerged} />,
      { permissions: ['customers.merge'] }
    );

    await user.click(screen.getByRole('radio', { name: /ada lovelace survives/i }));
    await user.click(screen.getByRole('button', { name: /^merge$/i }));

    await waitFor(() => expect(screen.getByText(/already merged/i)).toBeInTheDocument());
    // onMerged is used by the parent to close the dialog + refetch duplicates.
    expect(onMerged).toHaveBeenCalledTimes(1);
  });
});
