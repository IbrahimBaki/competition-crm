import { describe, it, expect } from 'vitest';
import { toBranch, toDepartment, toTeam, toBranchWorkingHour, toBranchHoliday } from '../wire';

describe('admin organisation wire mappers', () => {
  it('toBranch maps BranchResource payload (single-locale name, not bilingual)', () => {
    const branch = toBranch({
      id: 'b-1',
      name: 'Cairo Branch',
      code: 'cai',
      timezone: 'Africa/Cairo',
      is_24_7: false,
      is_active: true,
      created_at: '2026-01-01T00:00:00+00:00',
      updated_at: '2026-01-01T00:00:00+00:00',
    });

    expect(branch.id).toBe('b-1');
    expect(typeof branch.id).toBe('string');
    expect(branch.name).toBe('Cairo Branch');
    expect(branch.is24x7).toBe(false);
    expect(branch.isActive).toBe(true);
  });

  it('toBranch treats a missing optional field as a safe default, not a crash', () => {
    const branch = toBranch({ id: 'b-2', name: 'Minimal', code: 'min', timezone: 'UTC' });
    expect(branch.is24x7).toBe(false);
    expect(branch.createdAt).toBeNull();
  });

  it('toDepartment keeps branch_id as branchId string', () => {
    const department = toDepartment({
      id: 'd-1',
      branch_id: 'b-1',
      name: 'Support',
      code: 'sup',
      is_active: true,
    });
    expect(department.branchId).toBe('b-1');
    expect(department.name).toBe('Support');
  });

  it('toTeam keeps department_id as departmentId string', () => {
    const team = toTeam({
      id: 't-1',
      department_id: 'd-1',
      name: 'Tier 1',
      code: 't1',
      is_active: false,
    });
    expect(team.departmentId).toBe('d-1');
    expect(team.isActive).toBe(false);
  });

  it('toBranchWorkingHour maps a working day', () => {
    const day = toBranchWorkingHour({
      day_of_week: 1,
      is_working: true,
      opens_at: '09:00',
      closes_at: '17:00',
    });
    expect(day).toEqual({ dayOfWeek: 1, isWorking: true, opensAt: '09:00', closesAt: '17:00' });
  });

  it('toBranchWorkingHour maps a disabled day with null hours', () => {
    const day = toBranchWorkingHour({ day_of_week: 5, is_working: false, opens_at: null, closes_at: null });
    expect(day.isWorking).toBe(false);
    expect(day.opensAt).toBeNull();
  });

  it('toBranchHoliday maps a one-time date holiday', () => {
    const holiday = toBranchHoliday({
      id: 'h-1',
      name: 'National Day',
      date: '2026-07-23',
      recurring_month_day: null,
    });
    expect(holiday.date).toBe('2026-07-23');
    expect(holiday.recurringMonthDay).toBeNull();
  });

  it('toBranchHoliday maps a recurring month-day holiday', () => {
    const holiday = toBranchHoliday({
      id: 'h-2',
      name: "New Year's Day",
      date: null,
      recurring_month_day: '01-01',
    });
    expect(holiday.date).toBeNull();
    expect(holiday.recurringMonthDay).toBe('01-01');
  });
});
