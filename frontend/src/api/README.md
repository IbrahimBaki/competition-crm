# API Client

`frontend/src/api/generated/` is produced by orval from `docs/api/openapi.yaml`.

**Never edit it by hand.** Re-run `npm run api:generate` after any contract change and commit the diff.

## Regenerating the Client

```bash
npm run api:generate
```

This will:
1. Read `../docs/api/openapi.yaml`
2. Generate hooks into `src/api/generated/endpoints.ts`
3. Generate types into `src/api/generated/model/`
4. Use the mutator at `src/api/http/mutator.ts` for all requests

The generated code includes proper TypeScript types, TanStack Query integration, and automatic error handling via the mutator.

## API Transport

All HTTP requests flow through `src/api/http/mutator.ts`, which handles:
- CSRF cookie bootstrap and retry (419)
- Session recovery and re-authentication (401)
- Error normalization
- Envelope unwrapping
- Locale header attachment

Feature code should **never** import axios directly. Always use the generated hooks.
