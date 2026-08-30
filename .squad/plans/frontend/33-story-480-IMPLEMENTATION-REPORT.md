# Story 480 (FE-03: Ticket List and Ticket Detail Workspace) — Implementation Report

**Date**: 2026-08-28
**Status**: Complete
**Branch**: dev

## Summary

Ticket list (`/tickets`) and ticket detail (`/tickets/:ticketId`) workspace,
per `.squad/plans/frontend/33-story-480.md`. All Done Criteria items are
checked off in that plan file.

## Branch taken: bulk actions

**No bulk endpoint exists.** `grep -rn bulk routes/api.php app/Domains/Ticketing/`
returns nothing. Per the plan's own documented fallback for this case,
`TicketBulkActions.tsx` implements bulk assign / bulk status change as a
**client-side sequenced fan-out** over the existing per-ticket endpoints
(`POST /tickets/{ticket}/assign`, `POST /tickets/{ticket}/status`), bounded
to 4 concurrent requests (`runWithConcurrency.ts`), with a live "N of M
processed" readout and a final per-ticket success/failure summary. The list
query is invalidated once, after the whole batch completes — not per item.
"Bulk tag" from the plan's task list was **not** implemented: there is no
tag-assignment endpoint on the backend at all (no route, despite the
`tickets.tag` permission key existing) — see `.squad/gaps/33-480.md` §4.

## Branch taken: saved views

**No saved-view endpoint exists.** `TicketSavedView`, `SaveTicketView`,
`DeleteTicketView`, and `TicketSavedViewResource` all exist under
`app/Domains/Ticketing/`, but no route exposes them in `routes/api.php`. Per
the plan's documented fallback, `SavedViewsBar.tsx` stores saved views in
`localStorage` only (key `tickets.savedViews.v1`), scoped per browser. There
is a code comment on the storage key pointing back to this decision. This
should move server-side once the endpoint ships — the swap only touches
`SavedViewsBar.tsx`.

## Other things worth knowing (full detail in `.squad/gaps/33-480.md`)

- The generated API client's body types don't match several FormRequests
  (status change, assign, transfer, reopen/spam, links, messages). The UI is
  wired to the real FormRequests via `frontend/src/features/tickets/api/wire.ts`,
  which documents every mismatch inline.
- Two backend fatal-error bugs were fixed as part of this story (both
  approved): `ApiResponse::created()` was missing entirely (broke status
  change, reopen, mark/restore spam, link creation — 15 call sites across 8
  controllers); `TicketController::history()` passed an invalid named
  argument to `ApiResponse::collection()` (broke the history endpoint
  outright).
- The list/queue/message-list/link-list/history endpoints return raw,
  unwrapped Eloquent models rather than their Resources — the list table and
  history panel are built against that raw shape; customer/assignee/category
  names and SLA data are not renderable from the list endpoint because
  they're simply not in the response.
- `usePermissions.ts`'s `canAny` had a parameter-shadowing bug that made
  every `anyPermission` gate a no-op; fixed.
- `php artisan test --filter=TicketQueueTest` (and likely other ticketing
  tests using the same helper) fails on `User::grantPermission()`, which
  doesn't exist anywhere in the codebase. Pre-existing, unrelated to this
  story — not fixed here.
