# ADR 009: RTL and localization architecture

## Context

The product supports English and Arabic through i18next, but V1 contains hardcoded visible strings and fallback maps in primitives.

## Decision

**Status: Accepted**

Retain i18next/react-i18next. All V2 visible and assistive text uses semantic translation keys from creation. Use logical CSS properties and test English LTR plus Arabic RTL for every V2 component.

## Alternatives considered

Separate portal/staff localization systems, hardcoded bilingual fallback maps, or an i18n replacement.

## Consequences

Status/value labels need explicit domain translations. Directional icons mirror; semantic icons do not. Arabic expansion, keyboard behavior, drawers, tables and overlays are first-class acceptance criteria.

## Migration implications

Existing EN/AR resources are migration input, not a license to copy gaps. Retire primitive fallback maps with V1 primitive replacement.
