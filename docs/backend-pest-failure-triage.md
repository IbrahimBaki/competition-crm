# Backend Pest Failure Triage

Live triage for the production frontend rebuild. Failures are not skipped, assertions are not weakened, and valid authorization remains enabled.

## Current-work targeted coverage

| Group | Result | Classification |
|---|---|---|
| Knowledge category UUID API contract | PASS — 2 tests, 8 assertions | Current-work regression coverage |
| Report scope and catalogue endpoint | PASS — 6 tests, 7 assertions | Current-work browser-discovered authorization/response regression coverage |
| Full Pest suite | FAIL — 205 failed, 414 passed, 3 skipped, 2 risky; 1967 assertions | Mixed legacy fixture/environment failures and active-domain failures; release gate remains closed |

## Confirmed failure groups

| Group | Symptom | Classification | Required disposition |
|---|---|---|---|
| `AuthorizationEnforcementTest` branch create cases | Expected 403/201, receives validation 422 | Stale fixture: payload sends `display_name`; active branch request validates bilingual `name` and other current fields before controller authorization | Correct fixture after confirming the complete request contract; do not bypass validation or authorization |
| Unit tests without Laravel bootstrapping | Missing model resolver/container bindings | Test-environment architecture issue documented by the prior full run | Convert genuinely framework-dependent unit fixtures to boot the application; do not change production models for isolated-test assumptions |
| Legacy database fixtures | Columns such as `value_normalised` or `invited_by_uuid` do not match active schema | Stale fixture/schema mismatch | Update fixtures to current migrations when the affected workflow is active |
| Permissionless feature fixtures | Tests create users without required permissions then expect protected endpoints to succeed | Incorrect authorization fixture | Grant the exact permission required by the production route |

## Environment constraint

The host filesystem reported 100% usage during real-browser verification. Docker remains the canonical PHP runtime, but a fresh full-suite result must be captured after enough host space is available for Laravel/Pest temporary and result files. This constraint does not convert any failure to PASS.
