# V2 Gate 2.5 Dependency Installation Report

## Installed Packages

| Package | Type | Resolved version |
|---|---|---:|
| `@radix-ui/react-dialog` | production | 1.1.23 |
| `@radix-ui/react-alert-dialog` | production | 1.1.23 |
| `@radix-ui/react-dropdown-menu` | production | 2.1.24 |
| `@radix-ui/react-popover` | production | 1.1.23 |
| `@radix-ui/react-tooltip` | production | 1.2.16 |
| `@radix-ui/react-tabs` | production | 1.1.21 |
| `lucide-react` | production | 1.39.0 |
| `vitest-axe` | development | 0.1.0 |

Only the approved direct dependencies were added. Expected transitive packages include Radix interaction/focus/portal dependencies, Floating UI, scroll locking utilities, and `vitest-axe` dependencies including `axe-core` 4.13.0.

## Files Changed

- `package.json` — adds exactly the seven approved production dependencies and `vitest-axe` development dependency.
- `package-lock.json` — records those direct dependencies and their expected transitives.
- `V2_GATE2_DEPENDENCY_INSTALL_REPORT.md` — this report.

No application runtime, UI, configuration, route, API, authentication, generated-client, or Tailwind files changed in this task.

## Lockfile Validation

- No unexpected direct dependencies were added.
- No existing direct dependency was intentionally upgraded.
- React remains 18.3.1.
- React DOM remains 18.3.1.
- Vite remains 5.4.21.
- TypeScript remains 5.9.3.
- Zod remains 3.25.76 in the lockfile (with unchanged `package.json` range `^3.22.4`).
- npm changed `dev` to `devOptional` metadata for shared React type packages and removed the `dev` marker from shared `tslib`; these are expected lockfile classification changes caused by production Radix transitives, not version upgrades.
- No surprising major-version transitive shift was identified.

## Validation

| Check | Result |
|---|---|
| Typecheck | PASS — `npm run typecheck` completed without errors. |
| Lint | PASS — `npm run lint` completed without warnings/errors. |
| Build | PASS — `npm run build` completed with Vite 5.4.21. |
| Tests | BASELINE ONLY — 116/118 tests passed; the only failures are the two known admin mapper tests below. No new dependency-related failures were observed. |

## Baseline Test Failures

`PRE-EXISTING — NOT CAUSED BY V2 DEPENDENCIES`

`src/features/admin/api/__tests__/wire.test.ts` continues to fail in exactly two cases:

1. `toBranch maps BranchResource payload (single-locale name, not bilingual)` expects `Cairo Branch` but receives an empty string.
2. `toDepartment keeps branch_id as branchId string` expects `Support` but receives an empty string.

These failures predate Gate 2.5 and were not modified or fixed here. Existing React `act` and React Router future-flag warnings also remain baseline test output.

## Unexpected Changes

None.

## Dependency Security Notices

The `vitest-axe` installation reported 25 npm audit vulnerabilities: 4 moderate, 10 high, and 11 critical. npm suggested `npm audit fix` and `npm audit fix --force`; neither command was run. No dependency remediation or unrelated upgrade is included in this migration task.

GATE 2 DEPENDENCY INSTALLATION: PASS
