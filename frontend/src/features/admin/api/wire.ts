// Adapter layer between the admin UI and the admin HTTP API, following the
// wire-mapper pattern in frontend/src/features/customers/api/wire.ts:
// generated response in, hand-written domain type out; generated types never
// leak into components.
//
// Several generated request-body types are typed as `{ [key: string]: unknown }`
// because the OpenAPI spec is generic/drifted for these endpoints (confirmed
// by reading the real Laravel FormRequests). This file's mutation hooks build
// the real payload shape from the FormRequest `rules()` methods directly, not
// from the generated body type. See .squad/gaps/frontend/36-483.md.
//
// Organisation resources (Branch/Department/Team/BranchHoliday) return `name`
// as a SINGLE locale-resolved string on read (`BilingualString::forLocale()`
// server-side) even though create/update require `{ar, en}` — see
// .squad/gaps/frontend/36-483.md #1. There is no bilingual read shape for
// these four resources; forms must re-collect the non-active locale on edit.
//
// Automation rule create/update/delete and SLA policy CRUD have no generated
// client functions at all (spec drift — the routes exist in routes/api.php
// but were never captured in the frozen OpenAPI spec orval reads). Those
// mutations call `apiRequest` directly, same envelope conventions as every
// generated function.

import { useMutation, useQueryClient, type UseMutationOptions } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import {
  postBranches,
  putBranch,
  deleteBranch,
  postBranchActivate,
  postBranchDeactivate,
  putBranchWorkingHours,
  postBranchHolidays,
  putBranchHoliday,
  deleteBranchHoliday,
  postDepartments,
  putDepartment,
  deleteDepartment,
  postDepartmentActivate,
  postDepartmentDeactivate,
  postTeams,
  putTeam,
  deleteTeam,
  postTeamActivate,
  postTeamDeactivate,
  getGetBranchesQueryKey,
  getGetBranchQueryKey,
  getGetBranchWorkingHoursQueryKey,
  getGetBranchHolidaysQueryKey,
  getGetDepartmentsQueryKey,
  getGetDepartmentQueryKey,
  getGetTeamsQueryKey,
  getGetTeamQueryKey,
} from '@/api/generated/organization/organization';
import {
  postUsersInvite,
  postUserActivate,
  postUserDeactivate,
  postUserAttachBranch,
  deleteUserDetachBranch,
  postUserSetPrimaryBranch,
  postUserAttachDepartment,
  deleteUserDetachDepartment,
  postRoles,
  putRole,
  deleteRole,
  postRoleAttachUser,
  deleteRoleDetachUser,
  putAuthPolicy,
  getGetUsersQueryKey,
  getGetRolesQueryKey,
  getGetRoleQueryKey,
  getGetAuthPolicyQueryKey,
} from '@/api/generated/security/security';
import type {
  Branch,
  BranchWorkingHour,
  BranchHoliday,
  Department,
  Team,
  BilingualText,
  AdminUser,
  Invitation,
  Role,
  PermissionCatalogueGroup,
  TicketStatusDefinition,
  TicketStatusLifecycleType,
  TicketCategory,
  TicketCategoryField,
  TicketCategoryFieldType,
} from '../types';

const JSON_HEADERS = { 'Content-Type': 'application/json' } as const;

// --- Mappers (pure, no React) ------------------------------------------

function str(value: unknown): string {
  if (typeof value === 'string') return value;
  if (value && typeof value === 'object') {
    const bilingual = value as { ar?: unknown; en?: unknown };
    if (typeof bilingual.ar === 'string' && bilingual.ar.trim()) return bilingual.ar;
    if (typeof bilingual.en === 'string' && bilingual.en.trim()) return bilingual.en;
  }
  return '';
}

function strOrNull(value: unknown): string | null {
  return typeof value === 'string' ? value : null;
}

function num(value: unknown): number {
  return typeof value === 'number' ? value : Number(value ?? 0);
}

function bilingual(value: unknown): { ar: string; en: string } {
  if (!value || typeof value !== 'object') return { ar: '', en: '' };
  const data = value as { ar?: unknown; en?: unknown };
  return { ar: typeof data.ar === 'string' ? data.ar : '', en: typeof data.en === 'string' ? data.en : '' };
}

export function toBranch(raw: unknown): Branch {
  const data = (raw ?? {}) as Record<string, unknown>;
  const name = bilingual(data.name);
  return {
    id: str(data.id),
    name: name.ar || name.en,
    nameAr: name.ar,
    nameEn: name.en,
    code: str(data.code),
    timezone: str(data.timezone),
    is24x7: Boolean(data.is_24_7),
    isActive: Boolean(data.is_active),
    createdAt: strOrNull(data.created_at),
    updatedAt: strOrNull(data.updated_at),
  };
}

export function toDepartment(raw: unknown): Department {
  const data = (raw ?? {}) as Record<string, unknown>;
  const name = bilingual(data.name);
  return {
    id: str(data.id),
    branchId: str(data.branch_id),
    name: name.ar || name.en,
    nameAr: name.ar,
    nameEn: name.en,
    code: str(data.code),
    isActive: Boolean(data.is_active),
    createdAt: strOrNull(data.created_at),
    updatedAt: strOrNull(data.updated_at),
  };
}

export function toTeam(raw: unknown): Team {
  const data = (raw ?? {}) as Record<string, unknown>;
  const name = bilingual(data.name);
  return {
    id: str(data.id),
    departmentId: str(data.department_id),
    name: name.ar || name.en,
    nameAr: name.ar,
    nameEn: name.en,
    code: str(data.code),
    isActive: Boolean(data.is_active),
    createdAt: strOrNull(data.created_at),
    updatedAt: strOrNull(data.updated_at),
  };
}

export function toBranchWorkingHour(raw: unknown): BranchWorkingHour {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    dayOfWeek: num(data.day_of_week),
    isWorking: Boolean(data.is_working),
    opensAt: strOrNull(data.opens_at),
    closesAt: strOrNull(data.closes_at),
  };
}

export function toBranchHoliday(raw: unknown): BranchHoliday {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    id: str(data.id),
    name: str(data.name),
    date: strOrNull(data.date),
    recurringMonthDay: strOrNull(data.recurring_month_day),
    createdAt: strOrNull(data.created_at),
    updatedAt: strOrNull(data.updated_at),
  };
}

// --- Security: users, roles, permissions -----------------------------------

/**
 * `UserLifecycleController::index()` paginates raw `User::query()` columns
 * (not `UserResource`) — see ../types.ts doc comment on `AdminUser`. Status
 * is derived from `deactivated_at`/`is_active` defensively since the exact
 * raw column set was not empirically verified against a live response.
 */
export function toAdminUser(raw: unknown): AdminUser {
  const data = (raw ?? {}) as Record<string, unknown>;
  const isActive = data.is_active !== undefined ? Boolean(data.is_active) : !data.deactivated_at;
  return {
    id: str(data.uuid ?? data.id),
    name: str(data.name),
    email: str(data.email),
    status: isActive ? 'active' : 'deactivated',
    createdAt: strOrNull(data.created_at),
  };
}

export function toInvitation(raw: unknown): Invitation {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    id: str(data.id),
    email: str(data.email),
    expiresAt: strOrNull(data.expires_at),
    acceptedAt: strOrNull(data.accepted_at),
    createdAt: strOrNull(data.created_at),
  };
}

export function toRole(raw: unknown): Role {
  const data = (raw ?? {}) as Record<string, unknown>;
  const displayName = (data.display_name ?? {}) as Record<string, unknown>;
  return {
    id: str(data.id),
    name: str(data.name),
    displayName: { ar: str(displayName.ar), en: str(displayName.en) },
    isSystem: Boolean(data.is_system),
    permissionKeys: Array.isArray(data.permission_keys) ? data.permission_keys.map(String) : [],
    createdAt: strOrNull(data.created_at),
    updatedAt: strOrNull(data.updated_at),
  };
}

/**
 * `PermissionCatalogueController::show()` returns `{data: {module: [{key,
 * action, scope}]}}` — an object keyed by module, not an array. See
 * .squad/gaps/36-483.md #2: there is no description field.
 */
export function toPermissionCatalogue(raw: unknown): PermissionCatalogueGroup[] {
  const data = (raw ?? {}) as Record<string, unknown>;
  return Object.entries(data)
    .filter(([, entries]) => Array.isArray(entries))
    .map(([module, entries]) => ({
      module,
      entries: (entries as unknown[]).map((entry) => {
        const e = (entry ?? {}) as Record<string, unknown>;
        return { key: str(e.key), action: str(e.action), scope: strOrNull(e.scope) };
      }),
    }));
}

// --- Branches --------------------------------------------------------------

export interface CreateBranchVars {
  name: BilingualText;
  code: string;
  timezone: string;
  is24x7: boolean;
}

export function useCreateBranch(options?: UseMutationOptions<Branch, unknown, CreateBranchVars>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ name, code, timezone, is24x7 }) =>
      postBranches(
        { name, code, timezone, is_24_7: is24x7 } as unknown as Parameters<typeof postBranches>[0],
        { headers: JSON_HEADERS }
      ).then(toBranch),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetBranchesQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export interface UpdateBranchVars {
  branch: string;
  name?: BilingualText;
  code?: string;
  timezone?: string;
  is24x7?: boolean;
}

export function useUpdateBranch(options?: UseMutationOptions<void, unknown, UpdateBranchVars>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ branch, name, code, timezone, is24x7 }) =>
      putBranch(
        branch,
        { name, code, timezone, is_24_7: is24x7 } as unknown as Parameters<typeof putBranch>[1],
        { headers: JSON_HEADERS }
      ),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetBranchesQueryKey() });
      queryClient.invalidateQueries({ queryKey: getGetBranchQueryKey(args[1].branch) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useDeleteBranch(options?: UseMutationOptions<void, unknown, { branch: string }>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ branch }) => deleteBranch(branch),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetBranchesQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useSetBranchActive(
  options?: UseMutationOptions<void, unknown, { branch: string; active: boolean }>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ branch, active }) =>
      active ? postBranchActivate(branch) : postBranchDeactivate(branch),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetBranchesQueryKey() });
      queryClient.invalidateQueries({ queryKey: getGetBranchQueryKey(args[1].branch) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

// --- Branch working hours ----------------------------------------------

export interface ReplaceBranchWorkingHoursVars {
  branch: string;
  days: BranchWorkingHour[];
}

/** Replace semantics — always send all seven days, never a partial diff. */
export function useReplaceBranchWorkingHours(
  options?: UseMutationOptions<void, unknown, ReplaceBranchWorkingHoursVars>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ branch, days }) =>
      putBranchWorkingHours(
        branch,
        {
          days: days.map((day) => ({
            day_of_week: day.dayOfWeek,
            is_working: day.isWorking,
            opens_at: day.isWorking ? day.opensAt : null,
            closes_at: day.isWorking ? day.closesAt : null,
          })),
        } as unknown as Parameters<typeof putBranchWorkingHours>[1],
        { headers: JSON_HEADERS }
      ),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetBranchWorkingHoursQueryKey(args[1].branch) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

// --- Branch holidays ------------------------------------------------------

export interface BranchHolidayVars {
  branch: string;
  name: BilingualText;
  date: string | null;
  recurringMonthDay: string | null;
}

export function useCreateBranchHoliday(
  options?: UseMutationOptions<BranchHoliday, unknown, BranchHolidayVars>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ branch, name, date, recurringMonthDay }) =>
      postBranchHolidays(
        branch,
        { name, date, recurring_month_day: recurringMonthDay } as unknown as Parameters<
          typeof postBranchHolidays
        >[1],
        { headers: JSON_HEADERS }
      ).then(toBranchHoliday),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetBranchHolidaysQueryKey(args[1].branch) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useUpdateBranchHoliday(
  options?: UseMutationOptions<void, unknown, BranchHolidayVars & { holiday: string }>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ branch, holiday, name, date, recurringMonthDay }) =>
      putBranchHoliday(
        branch,
        holiday,
        { name, date, recurring_month_day: recurringMonthDay } as unknown as Parameters<
          typeof putBranchHoliday
        >[2],
        { headers: JSON_HEADERS }
      ),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetBranchHolidaysQueryKey(args[1].branch) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useDeleteBranchHoliday(
  options?: UseMutationOptions<void, unknown, { branch: string; holiday: string }>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ branch, holiday }) => deleteBranchHoliday(branch, holiday),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetBranchHolidaysQueryKey(args[1].branch) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

// --- Departments -------------------------------------------------------

export interface CreateDepartmentVars {
  branchId: string;
  name: BilingualText;
  code: string;
}

export function useCreateDepartment(
  options?: UseMutationOptions<Department, unknown, CreateDepartmentVars>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ branchId, name, code }) =>
      postDepartments(
        { branch_id: branchId, name, code } as unknown as Parameters<typeof postDepartments>[0],
        { headers: JSON_HEADERS }
      ).then(toDepartment),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetDepartmentsQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export interface UpdateDepartmentVars {
  department: string;
  name?: BilingualText;
  code?: string;
}

export function useUpdateDepartment(
  options?: UseMutationOptions<void, unknown, UpdateDepartmentVars>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ department, name, code }) =>
      putDepartment(department, { name, code } as unknown as Parameters<typeof putDepartment>[1], {
        headers: JSON_HEADERS,
      }),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetDepartmentsQueryKey() });
      queryClient.invalidateQueries({ queryKey: getGetDepartmentQueryKey(args[1].department) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useDeleteDepartment(
  options?: UseMutationOptions<void, unknown, { department: string }>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ department }) => deleteDepartment(department),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetDepartmentsQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useSetDepartmentActive(
  options?: UseMutationOptions<void, unknown, { department: string; active: boolean }>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ department, active }) =>
      active ? postDepartmentActivate(department) : postDepartmentDeactivate(department),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetDepartmentsQueryKey() });
      queryClient.invalidateQueries({ queryKey: getGetDepartmentQueryKey(args[1].department) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

// --- Teams ---------------------------------------------------------------

export interface CreateTeamVars {
  departmentId: string;
  name: BilingualText;
  code: string;
}

export function useCreateTeam(options?: UseMutationOptions<Team, unknown, CreateTeamVars>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ departmentId, name, code }) =>
      postTeams(
        { department_id: departmentId, name, code } as unknown as Parameters<typeof postTeams>[0],
        { headers: JSON_HEADERS }
      ).then(toTeam),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetTeamsQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export interface UpdateTeamVars {
  team: string;
  name?: BilingualText;
  code?: string;
}

export function useUpdateTeam(options?: UseMutationOptions<void, unknown, UpdateTeamVars>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ team, name, code }) =>
      putTeam(team, { name, code } as unknown as Parameters<typeof putTeam>[1], {
        headers: JSON_HEADERS,
      }),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetTeamsQueryKey() });
      queryClient.invalidateQueries({ queryKey: getGetTeamQueryKey(args[1].team) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useDeleteTeam(options?: UseMutationOptions<void, unknown, { team: string }>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ team }) => deleteTeam(team),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetTeamsQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useSetTeamActive(
  options?: UseMutationOptions<void, unknown, { team: string; active: boolean }>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ team, active }) => (active ? postTeamActivate(team) : postTeamDeactivate(team)),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetTeamsQueryKey() });
      queryClient.invalidateQueries({ queryKey: getGetTeamQueryKey(args[1].team) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

// --- Users -------------------------------------------------------------

export function useInviteUser(options?: UseMutationOptions<Invitation, unknown, { email: string }>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ email }) =>
      postUsersInvite({ email } as unknown as Parameters<typeof postUsersInvite>[0]).then(toInvitation),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetUsersQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useSetUserActive(
  options?: UseMutationOptions<void, unknown, { user: string; active: boolean }>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ user, active }) => (active ? postUserActivate(user) : postUserDeactivate(user)),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetUsersQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

// Write-only: there is no endpoint to read a user's current branch/department
// placement (see .squad/gaps/36-483.md #5), so these mutations cannot be
// paired with a "currently attached" list.
export function useAttachUserToBranch(
  options?: UseMutationOptions<void, unknown, { user: string; branch: string }>
) {
  return useMutation({ mutationFn: ({ user, branch }) => postUserAttachBranch(user, branch), ...options });
}

export function useDetachUserFromBranch(
  options?: UseMutationOptions<void, unknown, { user: string; branch: string }>
) {
  return useMutation({ mutationFn: ({ user, branch }) => deleteUserDetachBranch(user, branch), ...options });
}

export function useSetPrimaryBranchForUser(
  options?: UseMutationOptions<void, unknown, { user: string; branch: string }>
) {
  return useMutation({ mutationFn: ({ user, branch }) => postUserSetPrimaryBranch(user, branch), ...options });
}

export function useAttachUserToDepartment(
  options?: UseMutationOptions<void, unknown, { user: string; department: string }>
) {
  return useMutation({
    mutationFn: ({ user, department }) => postUserAttachDepartment(user, department),
    ...options,
  });
}

export function useDetachUserFromDepartment(
  options?: UseMutationOptions<void, unknown, { user: string; department: string }>
) {
  return useMutation({
    mutationFn: ({ user, department }) => deleteUserDetachDepartment(user, department),
    ...options,
  });
}

// --- Roles & permissions ------------------------------------------------

export interface CreateRoleVars {
  name: string;
  displayName: BilingualText;
  permissionKeys: string[];
}

export function useCreateRole(options?: UseMutationOptions<Role, unknown, CreateRoleVars>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ name, displayName, permissionKeys }) =>
      postRoles(
        { name, display_name: displayName, permission_keys: permissionKeys } as unknown as Parameters<
          typeof postRoles
        >[0],
        { headers: JSON_HEADERS }
      ).then(toRole),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetRolesQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export interface UpdateRoleVars {
  role: string;
  displayName: BilingualText;
  permissionKeys: string[];
}

/** `name` (the slug) is set once on create and is never sent on update — `UpdateRoleRequest::rules()` has no `name` field. */
export function useUpdateRole(options?: UseMutationOptions<void, unknown, UpdateRoleVars>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ role, displayName, permissionKeys }) =>
      putRole(
        role,
        { display_name: displayName, permission_keys: permissionKeys } as unknown as Parameters<typeof putRole>[1],
        { headers: JSON_HEADERS }
      ),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetRolesQueryKey() });
      queryClient.invalidateQueries({ queryKey: getGetRoleQueryKey(args[1].role) });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

/** `RolePolicy::delete` denies this for any `is_system` role — see .squad/gaps/36-483.md #6. */
export function useDeleteRole(options?: UseMutationOptions<void, unknown, { role: string }>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ role }) => deleteRole(role),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetRolesQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

export function useAttachRoleToUser(
  options?: UseMutationOptions<void, unknown, { role: string; user: string }>
) {
  return useMutation({ mutationFn: ({ role, user }) => postRoleAttachUser(role, user), ...options });
}

export function useDetachRoleFromUser(
  options?: UseMutationOptions<void, unknown, { role: string; user: string }>
) {
  return useMutation({ mutationFn: ({ role, user }) => deleteRoleDetachUser(role, user), ...options });
}

// --- Auth policy (settings) -------------------------------------------
// AuthPolicyController manages exactly one field, `require_two_factor` — see
// .squad/gaps/36-483.md #11. There is no default-locale field on this or any
// other endpoint; the plan's "default locale" instruction is unbuildable.

export function useUpdateAuthPolicy(
  options?: UseMutationOptions<void, unknown, { requireTwoFactor: boolean }>
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ requireTwoFactor }) =>
      putAuthPolicy({ require_two_factor: requireTwoFactor } as unknown as Parameters<typeof putAuthPolicy>[0], {
        headers: JSON_HEADERS,
      }),
    onSuccess: (...args) => {
      queryClient.invalidateQueries({ queryKey: getGetAuthPolicyQueryKey() });
      options?.onSuccess?.(...args);
    },
    ...options,
  });
}

// --- Ticket Statuses -------------------------------------------------------
// Read-only for now; edits are blocked by the backend's role-based policy.

export function toTicketStatusDefinition(raw: unknown): TicketStatusDefinition {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    id: str(data.id),
    key: str(data.key),
    name: str(data.name),
    lifecycleType: str(data.lifecycle_type) as TicketStatusLifecycleType,
    isDefault: Boolean(data.is_default),
    isSystem: Boolean(data.is_system),
    isActive: Boolean(data.is_active),
    position: num(data.position),
    stopsSlaClock: Boolean(data.stops_sla_clock),
  };
}

// --- Ticket Categories  ---------------------------------------------------
// Read-only for now; edits limited by tree depth and category state.

export function toTicketCategory(raw: unknown): TicketCategory {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    id: str(data.id),
    code: str(data.code),
    name: {
      ar: strOrNull(data.name_ar) ?? '',
      en: strOrNull(data.name_en) ?? '',
    },
    parentId: strOrNull(data.parent_id),
    depth: num(data.depth),
    isActive: Boolean(data.is_active),
    fields: Array.isArray(data.fields) ? (data.fields as unknown[]).map(toTicketCategoryField) : [],
  };
}

export function toTicketCategoryField(raw: unknown): TicketCategoryField {
  const data = (raw ?? {}) as Record<string, unknown>;
  return {
    id: str(data.id),
    key: str(data.key),
    label: {
      ar: strOrNull(data.label_ar) ?? '',
      en: strOrNull(data.label_en) ?? '',
    },
    type: (str(data.type) as unknown as TicketCategoryFieldType) || 'text',
    isRequired: Boolean(data.is_required),
    options: Array.isArray(data.options) ? (data.options as string[]) : [],
    position: num(data.position),
  };
}

// --- Raw apiRequest escape hatch, used by ./wireExtras.ts for domains with
// no generated client coverage (SLA, automation rule CRUD). Exported so
// those modules share one axios entry point rather than importing the
// mutator directly.
export { apiRequest };
