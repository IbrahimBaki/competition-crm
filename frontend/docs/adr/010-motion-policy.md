# ADR 010: Motion policy

## Context

V1 has only CSS transitions and reduced-motion handling. V2 needs responsive premium feedback without slowing enterprise workflows.

## Decision

**Status: Accepted**

Use CSS-first purposeful motion with semantic tokens, 150–300 ms interaction durations, compositor-friendly properties, and a reduced-motion final state. Do not install Motion/Framer Motion now.

## Alternatives considered

Install Motion immediately, use decorative animation libraries, or avoid all transitions.

## Consequences

Micro, overlay, layout, and data-state motion remain non-blocking and cannot create layout shift. A future Motion decision requires demonstrated coordinated-transition needs and separate approval.

## Migration implications

Preserve V1 reduced-motion behavior while V2 token contracts are introduced. No animation library is part of the POC baseline.
