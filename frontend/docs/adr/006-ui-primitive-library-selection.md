# ADR 006: UI primitive library selection

## Context

The current handcrafted primitives provide useful behavior but incomplete menu/overlay accessibility. V2 needs branded, RTL-aware accessible foundations without adopting a pre-styled visual system.

## Decision

**Status: Accepted**

Use selective Radix UI primitives behind V2 design-system wrappers. Initial packages are Dialog, Alert Dialog, Dropdown Menu, Popover, Tooltip, and Tabs. Feature code imports V2 wrappers, not Radix packages. Lucide React is the one general icon source.

## Alternatives considered

Keep current handcrafted primitives; adopt all shadcn/ui; React Aria; Headless UI; or raw Radix use throughout features.

## Consequences

Radix provides interaction/accessibility mechanics while V2 retains ownership of APIs, CSS Modules, semantic tokens, motion, and RTL conventions. Each additional primitive requires demonstrated need; raw feature Radix imports are prohibited.

## Migration implications

Replace V1 overlays/menu presentation route by route after V2 wrappers and tests exist. No V1 component is removed by this ADR.
