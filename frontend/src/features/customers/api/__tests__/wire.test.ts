import { describe, it, expect } from 'vitest';
import {
  toCustomerDetail,
  toCustomerListRow,
  toCustomerContact,
  toCustomerNote,
  toCustomerAttachment,
  toTimelineEntry,
  toDuplicateCandidate,
} from '../wire';

describe('customers wire mappers', () => {
  it('toCustomerDetail maps CustomerResource payload and keeps ids as strings', () => {
    const detail = toCustomerDetail({
      uuid: 'c-1',
      name: 'Ada Lovelace',
      preferred_locale: 'en',
      status: 'active',
      blocked_reason: null,
      blocked_at: null,
      company_account: { uuid: 'ca-1', name: 'Acme', service_tier: 'gold' },
      contacts: [{ uuid: 'ct-1', type: 'email', value: 'ada@example.com', label: null, is_primary: true, verified_at: null }],
      created_at: '2026-01-01T00:00:00+00:00',
      updated_at: '2026-01-01T00:00:00+00:00',
    });

    expect(detail.uuid).toBe('c-1');
    expect(typeof detail.uuid).toBe('string');
    expect(detail.companyAccount).toEqual({ uuid: 'ca-1', name: 'Acme', serviceTier: 'gold' });
    expect(detail.contacts).toHaveLength(1);
    expect(detail.blockedReason).toBeNull();
  });

  it('toCustomerDetail treats a missing company account as null', () => {
    const detail = toCustomerDetail({ uuid: 'c-2', name: 'No Company', status: 'active', contacts: [] });
    expect(detail.companyAccount).toBeNull();
  });

  it('toCustomerListRow maps the raw Eloquent column shape (not CustomerResource)', () => {
    const row = toCustomerListRow({
      id: 42,
      uuid: 'c-3',
      merged_into_customer_id: null,
      company_account_id: null,
      name: 'Raw Row',
      name_normalised: 'raw row',
      preferred_locale: 'en',
      status: 'blocked',
      blocked_reason: 'fraud',
      blocked_at: '2026-01-01T00:00:00+00:00',
      blocked_by_user_id: 7,
      anonymised_at: null,
      merged_at: null,
      created_at: '2026-01-01T00:00:00+00:00',
      updated_at: '2026-01-01T00:00:00+00:00',
    });

    expect(row.uuid).toBe('c-3');
    expect(row.id).toBe(42);
    expect(row.status).toBe('blocked');
    expect(row.mergedIntoCustomerId).toBeNull();
    expect(row.blockedReason).toBe('fraud');
  });

  it('toCustomerListRow surfaces a non-null mergedIntoCustomerId for the merged badge', () => {
    const row = toCustomerListRow({ id: 1, uuid: 'c-4', name: 'Merged', status: 'active', merged_into_customer_id: 99 });
    expect(row.mergedIntoCustomerId).toBe(99);
  });

  it('toCustomerContact maps ContactResource fields and preserves null label', () => {
    const contact = toCustomerContact({
      uuid: 'ct-2',
      type: 'phone',
      value: '+201000000000',
      label: null,
      is_primary: false,
      verified_at: null,
    });
    expect(contact).toEqual({
      uuid: 'ct-2',
      type: 'phone',
      value: '+201000000000',
      label: null,
      isPrimary: false,
      verifiedAt: null,
    });
  });

  it('toCustomerNote maps body (not "content") and survives a null author', () => {
    const note = toCustomerNote({
      uuid: 'n-1',
      body: 'Called back, no answer',
      author_uuid: null,
      author_name: null,
      created_at: '2026-01-01T00:00:00+00:00',
    });
    expect(note.body).toBe('Called back, no answer');
    expect(note.authorUuid).toBeNull();
    expect(note.authorName).toBeNull();
  });

  it('toCustomerAttachment maps scan_state and size_bytes', () => {
    const attachment = toCustomerAttachment({
      uuid: 'a-1',
      original_name: 'contract.pdf',
      mime_type: 'application/pdf',
      size_bytes: 1024,
      scan_state: 'pending',
      created_at: '2026-01-01T00:00:00+00:00',
    });
    expect(attachment.scanState).toBe('pending');
    expect(attachment.sizeBytes).toBe(1024);
  });

  it('toTimelineEntry maps TimelineEntryResource fields exactly, including null payload', () => {
    const entry = toTimelineEntry({
      id: 't-1',
      source: 'note',
      type: 'note',
      occurred_at: '2026-01-01T00:00:00+00:00',
      actor_uuid: null,
      payload: null,
    });
    expect(entry).toEqual({
      id: 't-1',
      source: 'note',
      type: 'note',
      occurredAt: '2026-01-01T00:00:00+00:00',
      actorUuid: null,
      payload: null,
    });
  });

  it('toDuplicateCandidate maps nested customer/duplicate_customer parties (no invented "score" field)', () => {
    const candidate = toDuplicateCandidate({
      uuid: 'd-1',
      customer: { uuid: 'c-5', name: 'Survivor' },
      duplicate_customer: { uuid: 'c-6', name: 'Loser' },
      status: 'pending',
      rule: 'name_match',
      evidence: {},
      reviewed_at: null,
      created_at: '2026-01-01T00:00:00+00:00',
    });
    expect(candidate.customer).toEqual({ uuid: 'c-5', name: 'Survivor' });
    expect(candidate.duplicateCustomer).toEqual({ uuid: 'c-6', name: 'Loser' });
    expect((candidate as unknown as { score?: number }).score).toBeUndefined();
  });
});
