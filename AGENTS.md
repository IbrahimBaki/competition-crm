# Support CRM Frontend Rebuild

## Mission

Rebuild and rebrand the entire React frontend into a production-ready application.

The final frontend must:

- use the existing Laravel backend correctly
- preserve all required business functionality
- cover all pages and all user roles
- use UI UX Pro Max as the design intelligence source
- follow design-system/MASTER.md
- be responsive and accessible
- build successfully for production
- have no placeholder functionality
- have no disconnected screens
- have no fake data where a real backend endpoint exists

## Scope

Primary scope:
- frontend/
- frontend configuration
- frontend tests
- frontend API integration
- frontend runtime configuration

Backend code should be treated as the source of truth for:
- endpoints
- permissions
- roles
- validation rules
- business behavior

Backend changes are allowed only when a small configuration or integration fix is genuinely required, such as:
- CORS
- SPA hostname configuration
- authentication cookie/session configuration
- OpenAPI generation mismatch

Do not redesign backend business logic.

## Design System

Always read:

design-system/MASTER.md

before implementing UI.

The entire application must feel like one coherent product.

Do not design each page independently.

Use reusable:
- layouts
- navigation
- tables
- forms
- dialogs
- buttons
- cards
- badges
- alerts
- empty states
- loading states
- pagination
- filters
- breadcrumbs

## Quality Rules

Do not consider a page complete merely because it renders.

A page is complete only when:
- backend data loads
- actions work
- permissions work
- errors are handled
- loading states exist
- empty states exist
- forms validate
- responsive layout works
- TypeScript passes

Do not hide existing functionality just because the current UI is broken.

## Validation

After major changes run appropriate:
- lint
- TypeScript checks
- tests
- production build

Fix failures before finishing.

## Safety

Do not delete backend functionality.
Do not change API contracts unnecessarily.
Do not delete working features just to simplify the frontend.
Do not replace real integration with mocks.
