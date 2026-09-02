# ADR 011: Staff and portal shared design system

## Context

Current staff and portal shells appear as separate visual products, while they need coherent branding and shared accessibility behavior.

## Decision

**Status: Accepted**

Use one shared V2 design system: foundations, primitives, icon policy, accessibility, RTL and motion rules. Staff and portal may differ in density, navigation, layout, contextual themes and product patterns.

## Alternatives considered

Separate staff and portal component systems, a portal-only UI kit, or forcing identical layouts.

## Consequences

There is one Button/Input/Dialog family and one token architecture. Portal remains auth/API isolated; sharing presentation foundations does not relax staff/portal security boundaries.

## Migration implications

Migrate the staff shell and portal shell independently after shared primitives are approved. No portal token/session changes are implied.
