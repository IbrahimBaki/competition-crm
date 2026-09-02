# Summary

Read-only `npm audit --json` reports 25 findings: 4 moderate, 10 high, and 11 critical. The high/critical findings are all development/build/code-generation tooling paths that were already present in the pre-Gate-2.5 lockfile. `npm audit --omit=dev --json` reports only two moderate React Router findings and **zero high or critical production findings**.

Gate 2.5 introduced Radix, Lucide React, `vitest-axe`, `axe-core`, Floating UI, and expected accessibility/overlay transitives. None appears in the full audit’s high/critical vulnerability paths.

## Production Dependency Risk

`npm audit --omit=dev --json` result:

| Severity | Count | Path | Gate 2.5 attribution |
|---|---:|---|---|
| Critical | 0 | None | None |
| High | 0 | None | None |
| Moderate | 2 | `react-router-dom@6.30.6 → react-router@6.30.6` | Pre-existing; not a Gate 2.5 dependency. |

The two moderate advisories concern React Router open redirect / SSR hydration behavior. They are outside the requested high/critical attribution but confirm that the production graph is not vulnerability-free. Audit offers a React Router 7 major upgrade; it is not part of this task or Gate 3.

No approved Radix or Lucide package introduces a production critical/high finding.

## Dev Dependency Risk

The following table groups duplicate audit package entries by their common root path. “Fix available” records the audit’s availability classification; it is not a recommendation to run a fix now.

| Advisory | Severity | Vulnerable package | Dependency path | Production or dev-only | Newly introduced? | Fix available? |
|---|---|---|---|---|---|---|
| `GHSA-h526-wf6g-67jv` — unsanitized enum-description code injection; affected child package entries group here | Critical | `@orval/core@6.31.0` and Orval child packages | root dev `orval@6.31.0 → @orval/{core,angular,axios,fetch,hono,mock,query,swr,zod}` | Dev-only code generation | A — clearly pre-existing | Yes; audit reports a major Orval remediation. |
| `GHSA-f456-rf33-4626` — mock-generation code injection | High, grouped with the critical Orval root in npm output | `@orval/mock@6.31.0` | root dev `orval@6.31.0 → @orval/mock@6.31.0` | Dev-only code generation | A — clearly pre-existing | Yes; tied to Orval remediation. |
| `GHSA-r5fr-rjxr-66jc` — lodash template code injection | High | `lodash@4.17.23` | root dev `orval → @orval/core → @ibm-cloud/openapi-ruleset → @stoplight/spectral-functions → lodash` | Dev-only code generation | A — clearly pre-existing | Yes. |
| `GHSA-3ppc-4f35-3m26`, `GHSA-7r86-cg39-jmmj`, `GHSA-23c5-xmqv-rm74` — minimatch ReDoS advisories | High | `minimatch@9.0.3` | root dev `@typescript-eslint/{eslint-plugin,parser} → typescript-estree → minimatch` | Dev-only lint tooling | A — clearly pre-existing | Yes. |
| `GHSA-fx2h-pf6j-xcff` — Vite Windows alternate-path deny bypass | High | `vite@5.4.21` | root dev `vite`; also consumed by `vitest`/`vite-node` | Dev-only build/dev/test tooling | A — clearly pre-existing | Yes; audit reports a major Vite remediation. |
| `GHSA-5xrq-8626-4rwp` — Vitest UI server arbitrary file read/execute | Critical | `vitest@1.6.1` | root dev `vitest@1.6.1` | Dev-only test tooling | A — clearly pre-existing | Yes; audit reports a major Vitest remediation. |

The audit’s 11 critical package entries are duplicate package-level effects of the Orval root path plus Vitest; they are not 11 independent production attack paths. The audit’s high package entries similarly include affected parent/effect records for the lodash, minimatch, Vite, and Orval paths.

## Newly Introduced Vulnerabilities

**Classification B — introduced by Gate 2.5: none.**

Package-lock comparison against the pre-install `HEAD:frontend/package-lock.json` confirms the following packages were absent before Gate 2.5 and were added by the approved install:

```text
@radix-ui/react-dialog and expected Radix transitives
@floating-ui/react-dom
lucide-react
vitest-axe
axe-core
```

None is named by `npm audit --json` as high or critical. The full audit count was reported after installation, but the dependency graph evidence establishes that every high/critical vulnerable root (`orval`, `vite`, `vitest`, `@typescript-eslint/*`) already existed at the same resolved version before the install.

## Pre-existing Vulnerabilities

**Classification A — clearly pre-existing:** all high/critical groups in the Dev Dependency Risk table.

Evidence: pre-Gate-2.5 lockfile comparison shows unchanged resolved versions for `orval` 6.31.0, `@orval/core` 6.31.0, Vite 5.4.21, Vitest 1.6.1, `@typescript-eslint/eslint-plugin` 6.21.0, and `@typescript-eslint/parser` 6.21.0. Those are the roots of every high/critical finding. Their transitive vulnerable packages were present through those roots before Gate 2.5.

**Classification C — unable to determine:** none for high/critical findings. The lockfile/path evidence is sufficient for attribution.

## vitest-axe Assessment

`vitest-axe@0.1.0` is dev-only. Its installed direct runtime dependencies include `axe-core@4.13.0`, `lodash-es@4.18.1`, `aria-query`, `dom-accessibility-api`, `chalk`, and `redent`; no high/critical advisory path includes `vitest-axe`, `axe-core`, or any of those dependencies.

`vitest-axe` is **not** the source of the high/critical audit advisories. Its peer relationship to the pre-existing `vitest@1.6.1` does not make the Vitest advisory newly introduced; Vitest was already direct dev tooling at the same resolved version.

Decision: **KEEP**. It is dev-only, architecture-approved, and does not contribute an audited high/critical finding. Reconsider only if a future audit identifies a direct `vitest-axe`/`axe-core` advisory or the project separately upgrades test tooling.

## Radix Assessment

The approved Radix packages and their expected transitives—Dialog, Alert Dialog, Dropdown Menu, Popover, Tooltip, Tabs, `@floating-ui/react-dom`, focus/portal/presence primitives, and scroll-lock helpers—do not appear in high/critical audit results.

Radix introduces no audited production or dev high/critical finding in the current graph. It is not removable as a vulnerability remediation because it is not an advisory source.

## Lucide Assessment

`lucide-react@1.39.0` does not appear in high/critical audit results and has no audited high/critical transitive path. It introduces no production or dev high/critical risk in the current graph.

## Recommended Action

Proceed to Gate 3 without changing the approved architecture or packages. Create a separate, explicitly approved dependency-hardening task before broad infrastructure work to evaluate major-version upgrades for Orval, Vite/Vitest, ESLint TypeScript tooling, and React Router. That task must validate OpenAPI generation, test behavior, Vite configuration, and router contracts independently; it must not be bundled into brand/design-system work.

Do not run `npm audit fix` or `npm audit fix --force`: both can alter unrelated tooling and would obscure controlled migration attribution.

Production critical vulnerabilities: 0
Production high vulnerabilities: 0

Gate 2.5 dependencies introduce production critical/high risk: NO

vitest-axe decision:
KEEP

Gate 3 security blocker: NO

Ready for Gate 3: YES
