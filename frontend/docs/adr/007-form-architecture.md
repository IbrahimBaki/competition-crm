# ADR 007: Form architecture

## Context

Current forms use page-local controlled state while Zod is installed. The CRM has nested, bilingual, async, upload, and server-validated forms.

## Decision

**Status: Accepted**

Adopt React Hook Form with the existing Zod 3.22.4. Use `@hookform/resolvers@3.10.0`, not the latest resolver line, to avoid upgrading Zod. Server validation remains authoritative and is mapped through a shared `applyServerErrorsToForm` adapter.

## Alternatives considered

Keep controlled local state, Formik, or upgrade Zod to use the latest resolver.

## Consequences

Client schemas improve immediate feedback and typed values; backend messages/payload rules remain source of truth. Form primitives own labels, descriptions and error associations.

## Migration implications

Install only with the Forms POC. Prove on ticket/customer flows before migrating V1 forms; do not mass-convert forms.
