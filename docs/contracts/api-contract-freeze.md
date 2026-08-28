# API v1 Contract Freeze Policy

## Status

**Frozen as of**: 2026-08-27  
**Baseline**: `docs/api/openapi.v1.frozen.yaml`  
**Enforcement**: `tests/Feature/Api/ContractFreezeTest.php`

The v1 contract is **additive-only** from this date forward. Breaking changes are prohibited in v1; new versions (`v2`, `v3`) must be created for structural changes.

---

## What "Frozen" Means

Once v1 is signed off, the API contract is locked to ensure:
- Clients can upgrade without breaking
- Generated SDKs remain compatible
- Deprecation paths are controlled

### Allowed Changes (Additive)

✅ Add a new path (`/new-resource`)  
✅ Add a new HTTP method to an existing path (`POST /customers/{id}/deactivate`)  
✅ Add an optional request parameter  
✅ Add a new optional response field  
✅ Add a new enum value to a **response** field  
✅ Expand a response schema reference (e.g., `ApiTokenResponse` gains a new optional property)  
✅ Add new security schemes  
✅ Add new error response codes (4xx/5xx)  

### Breaking Changes (Forbidden)

❌ Remove a path  
❌ Remove an operation  
❌ Rename an `operationId`  
❌ Change an existing response field's type  
❌ Remove a response field (client might depend on it)  
❌ Narrow a response enum (values removed)  
❌ Make a request parameter required (was optional)  
❌ Add a required request field (old clients won't send it)  
❌ Change status codes (200→201, etc.)  
❌ Remove a security scheme  

---

## When Breaking Changes Are Necessary

If a breaking change is essential:

1. **Do NOT mutate v1**. Create a new versioned path or operation:
   - Option A: Versioned path: `/api/v2/customers` alongside `/api/v1/customers`
   - Option B: New operation: `GET /tickets/{id}/sla-v2` alongside `GET /tickets/{id}`
   - Option C: Create `api/v2` alongside `api/v1` (new routes file)

2. **Deprecate v1 gracefully**:
   - Announce deprecation with a sunset date (e.g., 12 months)
   - Emit `Deprecation` HTTP header on v1 responses
   - Provide migration docs in `/docs`

3. **Keep both versions running** until all clients migrate

---

## Change Process

### For Additive Changes (PRs, Merges)

1. **Update the spec** (`docs/api/openapi.yaml`) with the new path/field/operation
2. **Regenerate the client**: `npm run api:client` (or `make api-client`)
3. **Commit both spec and client in the same PR**
4. **Run tests**: `php artisan test --filter=ContractFreezeTest` must pass
5. **Lint**: `npm run api:lint` must pass
6. **Merge when tests pass**

### For Breaking Changes (Escalation)

1. Create an issue in your tracking system (e.g., Linear)
2. Tag it `breaking-change` and `api-v2`
3. Discuss with stakeholders (frontend team, mobile team, integrations team)
4. Design the new version in parallel (do not ship both at once)
5. Plan a deprecation window
6. Document migration path

---

## Re-Baselining the Frozen Contract

If the spec receives a legitimate additive change:

1. **Make the additive change** to `docs/api/openapi.yaml`
2. **Regenerate client** and **run tests**
3. **Confirm in PR review** that the change is additive (does not violate freeze)
4. **After approval, regenerate the baseline**:
   ```bash
   cp docs/api/openapi.yaml docs/api/openapi.v1.frozen.yaml
   git add docs/api/openapi.v1.frozen.yaml
   git commit -m "chore: re-baseline API contract after additive change"
   ```
5. **Only do this in the PR that adds the change** — baseline and change ship together

**Why together?** If you re-baseline before the PR is merged, the freeze test passes silently and the change can land unreviewed. Bundling them makes the review intentional.

---

## Enforcement

`ContractFreezeTest.php` runs on every PR and in CI:

```bash
php artisan test --filter=ContractFreezeTest
```

This test:
- ✅ Fails if a path is removed
- ✅ Fails if an operation is removed
- ✅ Fails if an operationId is renamed
- ✅ Fails if a response property is removed
- ✅ Passes if new paths are added
- ✅ Passes if new optional fields are added

**Bypass is not allowed** (no `@depends` or skips).

---

## Reference Documents

- `docs/api/openapi.yaml` — The living spec (always the source of truth)
- `docs/api/openapi.v1.frozen.yaml` — The signed-off baseline (updated only on re-baselining)
- `docs/contracts/conventions-digest.md` — Governing API conventions (unchanged by this freeze)
- `.squad/plans/extend/30-story-477.md` — The story that established this freeze
- `tests/Feature/Api/ContractFreezeTest.php` — Automated enforcement

---

## Questions & Escalations

- **Is my change breaking?** → Run `ContractFreezeTest` locally; it will tell you
- **My change passed the test but feels wrong** → It's additive, so it's allowed. Discuss in PR review.
- **We need a breaking change** → Create a v2 or new operation; do not mutate v1
- **How long is v1 frozen?** → Until explicitly superseded by v2 (minimum 12 months after deprecation announcement)
