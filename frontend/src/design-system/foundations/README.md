# Signal Ledger foundations

V2 components are used only inside `V2PortalBoundary`, which renders `data-ui="v2"` and is the required host for Radix portals. This keeps V2 variables and global rules isolated while V1 remains active.

- Use semantic `--ds-*` tokens only. Components must not assume a white surface or encode palette values.
- Light values ship now; token roles, not component overrides, make a future dark theme possible.
- The system uses IBM Plex Sans and IBM Plex Sans Arabic through the existing Google Fonts import policy. Arabic receives its own optical metrics and must never be reduced to fit Latin geometry.
- Use logical CSS properties, bidi isolation for operational values, and a direction-aware icon helper. Only directional icons mirror.
- Layers are foundation → primitive → composite → pattern. No design-system file imports features, API modules, or auth/permission logic.
- V2 feature UI will use wrappers, never raw Radix. Tailwind is layout-only; raw palette, radius, shadow, and animation utilities are forbidden in V2.
- Every interactive primitive needs a visible focus state, keyboard behaviour, and accessible naming. User-facing strings are supplied by callers/i18n.
