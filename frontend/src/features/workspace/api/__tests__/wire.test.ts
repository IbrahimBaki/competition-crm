import { describe, it, expect } from 'vitest';
import { mapAgentTask, toCreateTaskPayload, formatWithOffset, localDateTimeToWire } from '../wire';

describe('workspace wire mappers', () => {
  it('mapAgentTask maps all AgentTaskResource keys, including a missing ticket', () => {
    const task = mapAgentTask({
      uuid: 't-1',
      title: 'Follow up',
      description: null,
      state: 'open',
      due_at: '2026-08-27T08:42:20+00:00',
      completed_at: null,
      cancelled_at: null,
      is_overdue: true,
      owner: { uuid: 'u-1', name: 'Agent One' },
      created_at: '2026-08-01T00:00:00+00:00',
      updated_at: '2026-08-01T00:00:00+00:00',
    });

    expect(task.uuid).toBe('t-1');
    expect(task.state).toBe('open');
    expect(task.isOverdue).toBe(true);
    expect(task.owner).toEqual({ uuid: 'u-1', name: 'Agent One' });
    expect(task.ticket).toBeNull();
  });

  it('mapAgentTask maps ticket when present', () => {
    const task = mapAgentTask({
      uuid: 't-2',
      title: 'x',
      description: null,
      state: 'done',
      due_at: null,
      completed_at: '2026-08-02T00:00:00+00:00',
      cancelled_at: null,
      is_overdue: false,
      owner: { uuid: null, name: null },
      ticket: { uuid: 'tk-1', reference: 'TCK-1' },
      created_at: '2026-08-01T00:00:00+00:00',
      updated_at: '2026-08-01T00:00:00+00:00',
    });

    expect(task.ticket).toEqual({ uuid: 'tk-1', reference: 'TCK-1' });
    expect(task.owner).toEqual({ uuid: null, name: null });
  });

  it('formatWithOffset emits a literal numeric offset, not a Z suffix', () => {
    const date = new Date(2026, 7, 27, 8, 42, 20); // local time, month is 0-indexed
    const formatted = formatWithOffset(date);
    expect(formatted).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/);
    expect(formatted).not.toContain('Z');
  });

  it('toCreateTaskPayload emits owner_id, required fields, and null (not undefined) for absent optional fields', () => {
    const payload = toCreateTaskPayload({ ownerId: 'u-1', title: 'Task' });
    expect(payload.owner_id).toBe('u-1');
    expect(payload.title).toBe('Task');
    expect(payload.due_at).toBeNull();
    expect(payload.ticket_id).toBeNull();
    expect(payload.branch_id).toBeNull();
    expect(payload.due_in_working_time).toBe(false);
  });

  it('toCreateTaskPayload passes through an offset-bearing due_at unchanged', () => {
    const payload = toCreateTaskPayload({
      ownerId: 'u-1',
      title: 'Task',
      dueAt: '2026-08-27T08:42:20+00:00',
    });
    expect(payload.due_at).toBe('2026-08-27T08:42:20+00:00');
  });

  it('localDateTimeToWire converts a datetime-local value to an offset-bearing string', () => {
    const wire = localDateTimeToWire('2026-08-27T10:30');
    expect(wire).toMatch(/^2026-08-27T10:30:00[+-]\d{2}:\d{2}$/);
  });

  it('localDateTimeToWire returns null for an empty value', () => {
    expect(localDateTimeToWire('')).toBeNull();
  });
});
