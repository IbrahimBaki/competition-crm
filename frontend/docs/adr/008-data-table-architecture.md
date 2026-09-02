# ADR 008: Data table architecture

## Context

Tickets, customers, admin and reports use inconsistent native tables with server pagination/filtering and uneven mobile behavior.

## Decision

**Status: Accepted**

Create a custom typed V2 `DataTable` plus deliberate responsive `DataList` pattern. Filters live outside the table; sorting/pagination remain server-side. Do not install AG Grid or TanStack Table for the initial POC.

## Alternatives considered

Continue independent native tables, TanStack Table immediately, or AG Grid.

## Consequences

The V2 API must support typed columns, selection/bulk operations, permission-aware actions, async states, semantic table behavior, RTL and mobile list representation. TanStack Table is reconsidered after Tickets POC evidence.

## Migration implications

Tickets validates the abstraction first; migrate specialized tables only after parity tests cover query parameters, action visibility, and mobile behavior.
