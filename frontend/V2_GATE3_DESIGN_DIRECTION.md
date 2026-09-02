# Frontend V2 — Gate 3A Design Direction

**Status:** proposal for human approval. This is a visual strategy artifact only; it does not change the incumbent `design-system/MASTER.md`, application UI, routes, dependencies, API contracts, or backend.

## Research basis and scope

This proposal was made after reviewing the required audit, V2 architecture/Phase 0/Gate 2 reports, the incumbent design-system master, all available page overrides (none exist), the current login, staff and portal shells, workspace, ticket/customer list and detail surfaces, reporting, representative administration surfaces, and the i18n/RTL implementation. The existing system is useful evidence of current behavior, not a V2 visual mandate.

The product is a permission-aware, bilingual operations application. Its visual stress cases are the ticket list and ticket workbench, not a landing-page dashboard. V2 must preserve the existing auth, backend, permission, i18n, and feature behavior while replacing the presentation layer incrementally.

## 1. V2 design principles

1. **Density without compression.** Show the operational context needed to decide and act, with compact rows and controls but generous enough line-height, grouping, and hit areas to prevent mistakes.
2. **Attention follows urgency, not decoration.** SLA breach, blocked work, assignment gaps, and destructive actions outrank brand color, illustrations, and generic KPIs.
3. **One surface, one job.** A surface earns its boundary when it creates a real interaction or reading region; ordinary page grouping stays open.
4. **Actions reveal themselves by priority.** One primary action is visible at the point of work; frequent secondary actions stay reachable; rare or destructive actions move to a clearly labelled overflow/confirmation path.
5. **Status is semantic, restrained, and redundant.** Color never carries workflow meaning alone. Text, icon/shape, and placement make state understandable in Arabic, English, grayscale, and dense tables.
6. **Typography is operational infrastructure.** Arabic and English are peers. Names, IDs, dates, countdowns, and tables must scan accurately before headings are made expressive.
7. **Motion confirms rather than entertains.** Motion explains a menu, drawer, state update, or hierarchy change. It never delays a repetitive support task.
8. **Direction is a first-class layout variable.** Logical layout, bidi-safe metadata, directional icons, and RTL table behavior are designed into every primitive rather than patched after LTR.
9. **The system is premium through restraint.** Precise borders, calm contrast, deliberate type, and excellent states create quality—not gradients, glass, or oversized rounded cards.

## 2. Three distinct visual directions

### Direction A — Signal Ledger

**Brand personality:** precise, composed, authoritative, calm, lucid, durable, editorial.

**Design philosophy:** An editorial operations desk: warm-white canvas, near-ink typography, fine slate dividers, and a single deep teal action signal. Information is organized in horizontal bands and open planes rather than a field of tiles. It borrows the *discipline* of data-dense and Swiss-style research, not either product’s visual identity.

**Strengths:** Highest scanability for tickets, SLAs, audits, reporting, and long sessions. The visual restraint makes permission and status states credible, and it translates well to Arabic because it relies on spacing and weight rather than Latin-centric novelty.

**Weaknesses/risks:** Must be executed with enough tonal separation and type hierarchy to avoid feeling austere. Teal needs disciplined use so the UI does not become monochrome.

**Typography direction:** Pair **IBM Plex Sans** for Latin/UI/numerals with **IBM Plex Sans Arabic** for Arabic. Both are pragmatic, compact, contemporary, and comfortable in data-dense work. Tabular figures are enabled for numerical columns and clocks.

**Color direction:** Mineral neutrals with ink/navy text and a dark teal primary. Amber is reserved for attention, not CTA decoration; semantic colors are toned rather than fluorescent.

**Surface philosophy:** A lightly mineral canvas; white primary work planes; subtle rule lines; raised surfaces only for transient menus, dialogs, sticky action bars, and focused work areas. No ornamental glass.

**Density:** Default is dense-but-breathable; operational tables use 40px rows, with comfortable/compact user density options.

**Navigation:** A light, persistent rail with a quiet product masthead, group labels, a 3px active edge, and selected-row background. It is an information index, not a dark decorative slab. Topbar is a utility strip, not a second nav.

**Tables:** Flat, full-width work planes with a strong sticky header, understated row rules, a narrow teal active/selected marker, tabular numeric alignment, and inline actions on focus/hover.

**Forms:** Label-led fields on open layouts. Form sections use a heading/rule and grid—not a card for every field group. Inline error text and a top error summary are visible and stable.

**Dashboard:** A priority ledger first: a prominent “needs attention” rail, then queue/work lists, then selective trend/target views. Metrics are compact evidence, not a tiled KPI wall.

**Ticket workbench:** A broad central conversation/compose plane; a narrow, collapsible context rail; ticket identity and live SLA/action strip stay at the top. Related workflow controls are grouped by decision, not rendered as twelve panels.

**Login:** A centered, calm sign-in panel with an asymmetric information column on desktop: product wordmark, one concise operational promise, and a faint ledger/grid motif. No stock hero or sales sections.

**Portal:** Shared type, teal action, rules, and status language; more open space, fewer simultaneous controls, and a calm centered reading measure.

**Motion personality:** Crisp and nearly invisible: fades, state-color transitions, and small translate/opacity overlays. No scroll reveal or decorative transforms.

**Brand distinctiveness:** The combination of editorial rules, mineral canvas, teal signal, horizontal information bands, and bilingual Plex typographic discipline reads as a custom operations instrument rather than a dashboard kit.

**UI UX Pro Max rationale:** Its data-dense result recommends compact 8–12px internal spacing, sticky headers, 36px baseline rows, filtering, row highlighting, and low-cost interaction. Its minimalist/Swiss result supports high-contrast grids and minimal effects. This direction deliberately rejects its generic “hero + features + CTA” and glassmorphism recommendations as inappropriate for staff work.

### Direction B — Service Atelier

**Brand personality:** human, reassuring, thoughtful, warm, polished, collaborative, approachable.

**Design philosophy:** A refined service studio with parchment-tinted canvas, charcoal type, muted ocean primary, and subtle terracotta/amber highlights. It puts customer context and conversation warmth ahead of command-center severity.

**Strengths:** Strongest customer-facing portal relationship; messages, knowledge, and onboarding feel considerate. Can soften administration without becoming consumer-like.

**Weaknesses/risks:** Warm accents can compete with operational severity and increase visual fatigue in large tables. It needs especially strict status governance to avoid a friendly-but-busy rainbow.

**Typography direction:** **Source Sans 3** for English paired with **Noto Sans Arabic**. The pairing is generous and highly legible; careful line-height normalization is needed because Arabic needs more vertical room.

**Color direction:** Paper/stone neutrals, deep ocean primary, restrained clay accent, and muted semantic states. Accent appears in portal/support moments, not all staff actions.

**Surface philosophy:** Softly distinct paper planes and restrained 6–8px corners. Very low shadows; warm tinted callouts are used only for meaningful message/channel context.

**Density:** Comfortable default, compact tables available. Less ideal for teams continuously switching queues at high volume.

**Navigation:** Calm light rail, slightly more narrative section names, and customer-aware breadcrumbs.

**Tables:** Strong column rules, mild alternating grouped bands only where scanning long rows benefits; selected state uses an outline and marker rather than fill.

**Forms:** More explanatory help and progressive disclosure, especially in the portal and admin configuration.

**Dashboard:** Workload and customer impact paired; trend and SLA narrative summaries supplement numerical queues.

**Ticket workbench:** Conversation is the visual heart; customer identity is a concise, persistent companion rather than a detached card stack.

**Login:** A quiet split composition with warm canvas and a dignified form plane; the product speaks through service clarity, not marketing imagery.

**Portal:** Naturally strongest of the three: calm, help-first, and reassuring while retaining staff tokens.

**Motion personality:** Soft fades and continuity; channel/reply state shifts use gentle background tone changes.

**Brand distinctiveness:** A service-operations personality uncommon in severe enterprise products, but still sober through typography and low ornament.

**UI UX Pro Max rationale:** The CRM palette result recommends professional blue plus a complementary green; accessibility/form results favor visible labels, blur validation, local recovery, loading buttons, and linked error summaries. This direction takes the service quality, but limits its color breadth.

### Direction C — Operations Grid

**Brand personality:** vigilant, technical, decisive, efficient, intelligent, controlled, modern.

**Design philosophy:** A high-contrast command console with graphite text, cool-gray planes, electric cobalt as the active signal, and data visualization as a visible operating layer.

**Strengths:** Exceptional for queues, real-time-like SLA risk, filters, and large reporting surfaces. Clear at a glance for supervisors.

**Weaknesses/risks:** Most likely to resemble a generic admin dashboard or induce visual fatigue. Cobalt can dominate Arabic text and the portal may feel overly technical. Dark-sidebar temptation is high and is rejected.

**Typography direction:** **Inter** plus **Noto Sans Arabic**, with tabular figures. Neutral and efficient, but less distinctive than Direction A.

**Color direction:** Cool gray/white base, cobalt primary, cyan informational highlight, limited amber/red semantics.

**Surface philosophy:** Tighter grids, sharper 4px corners, clear dividers, almost no shadow. Charts and alert bands create the visual energy.

**Density:** Highest density; 36–40px data rows and compact toolbars.

**Navigation:** Compact navigation rail, mode switcher, utility-rich top bar, powerful command surface.

**Tables:** Data-grid-like: pinned controls, stronger headers, column state, dense selection/bulk toolbar.

**Forms:** Technical and compact; excellent for admin but needs warmth and guidance in customer flows.

**Dashboard:** Queue heat, SLA anomaly timeline, target bullets, and actionable exception lists.

**Ticket workbench:** Like a work console: identity/action strip, central thread, inspectable activity/properties drawers.

**Login:** Minimal security-oriented authentication surface; strong but comparatively impersonal.

**Portal:** Must deliberately relax density and saturation or it becomes unsuitable for self-service.

**Motion personality:** Fast directional drawer/menu motion and list-update crossfades, never data theatrics.

**Brand distinctiveness:** Strong operations premise, but needs exceptional custom typography and information composition to escape “generic blue dashboard.”

**UI UX Pro Max rationale:** The data-dense research supports this direction’s 12-column grid, compact controls, sticky headers, functional filters, table sorting, and low-cost feedback. Chart research supports line trends, bullet target views, direct labelling, and accessible table fallbacks; it also warns not to use color alone.

## 3. Scorecard and decision

| Criterion | Signal Ledger (A) | Service Atelier (B) | Operations Grid (C) |
| --- | ---: | ---: | ---: |
| Premium feel | 9 | 9 | 8 |
| Enterprise credibility | 10 | 8 | 9 |
| Brand distinctiveness | 9 | 8 | 7 |
| Long-session usability | 10 | 8 | 8 |
| Data-density compatibility | 10 | 7 | 10 |
| Tables/forms | 10 | 8 | 9 |
| Arabic/RTL compatibility | 10 | 9 | 8 |
| Portal adaptability | 8 | 10 | 7 |
| Motion potential | 8 | 8 | 8 |
| Implementation maintainability | 10 | 8 | 9 |
| Avoids generic SaaS look | 10 | 8 | 6 |

**Recommended direction: Signal Ledger.** It best meets the actual daily work: reliable scanning, high-frequency ticket action, long shifts, complex permissions, and bilingual density. Its portal risk is manageable through shared foundations with a lighter density tier; the risks of the other directions are more fundamental.

## 4. Recommended V2 visual north star — Signal Ledger

**Brand keywords:** editorial operations, composed authority, precise service, mineral clarity, deliberate signal.

**Emotional goal:** After several hours, a staff user should feel oriented, in control, and unhurried—not visually stimulated, buried, or uncertain where to act.

**Visual signature:**

- A mineral canvas with crisp white work planes and fine horizontal “ledger” rules.
- Deep teal is a scarce action/focus signal; a 3px edge or underline identifies current context and selection.
- An information-band hierarchy: identity/action strip → primary work plane → contextual rail, instead of floating card mosaics.
- Bilingual Plex typography with clear, compact metadata and tabular operational numbers.
- Status appears as quiet labelled markers with icon/text, not multicolour pills.

## 5. Candidate color strategy

| Role | Candidate | Use |
| --- | --- | --- |
| canvas | `#F6F7F5` | app background; long-session low-glare mineral white |
| primary surface | `#FFFFFF` | main work planes, inputs, dialogs |
| raised surface | `#FBFCFB` | menus, contextual panels, hover layers |
| strong text | `#16221F` | titles, primary body, critical table values |
| secondary text | `#46534F` | labels, metadata, descriptions |
| muted text | `#68756F` | tertiary metadata only; never essential body text |
| subtle border | `#DDE3DF` | rules, table rows, form boundaries |
| strong border | `#B9C6BF` | focus-adjacent separators, selected neutral states |
| primary action | `#006B5E` | principal actions, active nav edge, links where appropriate |
| primary action hover | `#00574D` | hover/pressed primary |
| brand accent | `#B86A22` | rare emphasis, portal highlight, non-semantic editorial cue |
| focus | `#007D6E` | 2–3px focus ring with light halo |
| success | `#176B4A` | resolved/healthy with text/icon |
| warning | `#966100` | approaching SLA or review required |
| danger | `#B42318` | destructive/failed/overdue urgent actions |
| info | `#1D5E91` | neutral system information |

Contrast must be measured against each final surface: normal text and action labels target WCAG AA 4.5:1 minimum; focus/border/icon boundaries target 3:1 minimum. `#46534F` on white and `#006B5E` on white require final automated contrast verification, as do every semantic foreground/background pair. Dense tables use strong text for identifying values and secondary text only for supporting metadata. Status colors stay consistent across languages; localized labels and icons supply the meaning.

The brand accent is **not** a second CTA color, a dashboard decoration, a badge default, a chart series default, or a replacement for warning. Use it only when a non-semantic editorial highlight is useful and not competing with priority/status meaning.

## 6. Typography system direction

Choose a deliberate paired family: **IBM Plex Sans** (Latin, numeric UI) + **IBM Plex Sans Arabic** (Arabic). This is preferable to retaining Plus Jakarta Sans because it gives both scripts a dedicated, operationally credible companion with clearer data character; it avoids forcing a Latin-first geometric face into Arabic UI.

- **Weights:** 400 body, 500 labels/navigation, 600 section/title/actions, 700 only for page title or critical emphasis. Avoid 800 in operational screens.
- **Headings:** compact and sentence case; 24–28px page title desktop, 18–20px section title, 14–16px component heading. No giant marketing headings.
- **Body/UI:** 14px default desktop body/UI; 16px minimum body/input on mobile; 12px only for nonessential metadata, never dense Arabic body copy.
- **Tables/numerics:** 13–14px; `font-variant-numeric: tabular-nums` for IDs, amounts, dates, SLA clocks, and column totals. Preserve locale-aware digits/date formatting and isolate mixed-direction IDs with bidi-safe spans.
- **Arabic metrics:** establish script-specific line-height tokens (Arabic roughly 1.6 for body/UI vs Latin 1.45–1.5) and test all control heights with Arabic labels before freezing metrics. Do not compensate by shrinking Arabic text.

## 7. Spacing and density philosophy

Use a 4px base rhythm with 8px as the common relationship unit. Offer product-level density preferences only after the V2 POC proves their value:

| Context | Comfortable | Default | Compact |
| --- | --- | --- | --- |
| staff shell/page gutter | 28–32px | 20–24px | 16px |
| tables | 48px rows | 40px rows | 36px rows |
| forms/control height | 44px | 40px | 36px desktop only |
| ticket workbench regions | 20px gap | 16px gap | 12px gap |
| portal | 24–32px rhythm | 20–24px rhythm | not exposed initially |

Default staff density is the baseline. The ticket workbench stays default even if table compact mode is selected; composer, messages, destructive choices, and Arabic writing need room. Portal intentionally uses comfortable/default density, not staff compact density.

## 8. Shape language

Use a modest, purposeful radius scale: **4px** small chips/inputs/table focus regions, **6px** buttons and controls, **8px** dialogs/drawers and genuinely bounded panels. Avoid fully rounded rectangular controls except avatars, circular icon buttons, and tiny count dots.

Cards are reserved for modules with an independent state/action boundary (e.g., an SLA exception, upload drop zone, or portal request summary). Pages, form sections, tables, filter bars, and ticket subregions remain open with spacing and rules. Badges are compact labels, not miniature cards; tags/chips are removable values, not a general status system.

## 9. Elevation and border language

Flat surfaces are the default. A subtle border separates table rows, form controls, navigation groups, and adjacent work regions. Raised surfaces are limited to popovers/menus, dialogs, mobile drawers, a sticky composer/action bar, and an explicitly focused work region. Use one soft low-elevation shadow for transient layers and a stronger overlay shadow for modals only. No card shadow as a default, no random shadow classes, and no backdrop blur except a functional modal scrim if visual testing supports it.

## 10. Iconography

Lucide is the sole application icon family. Default sizes: 16px inline/table/status, 18px navigation and standard controls, 20px primary toolbar, 24px only for empty/error state anchors. Use the configured Lucide stroke consistently (normally 1.75–2px); do not mix filled icon sets or emoji/text glyphs.

Icons accompany labels in navigation and high-frequency actions; icon-only buttons require an accessible name and a 40px desktop/44px touch hit target. Status icons reinforce text, never replace it. Decorative icons are rare and `aria-hidden`. Mirror only directional icons (back/forward, chevrons, sidebar collapse, ordered workflow arrows) in RTL; semantic symbols such as ticket, user, attachment, bell, search, and settings do not mirror.

## 11. Application shell direction

Desktop has a 248px light sidebar, a 64px compact top utility bar, and a content area with responsive 20–32px gutters. The sidebar holds a small wordmark area, permission-filtered navigation groups, active edge marker, and a separated account/help region; it is not a dark visual block. Collapsed desktop state (72px) is acceptable only with labelled tooltips and a user preference; never make the primary navigation icon-only by default.

The topbar provides context/breadcrumbs, global search/command access, create/contextual action, notifications, locale, and account—not duplicate product navigation. Breadcrumbs appear at three or more hierarchy levels and maintain logical order in RTL. Global command/search is justified for ticket/customer IDs, queues, and navigable actions, but starts as a deliberate accessible command surface rather than an ornamental omnibox.

At mobile widths, navigation becomes a labelled drawer with scrim and focus management; the current page title/action remains in the sticky topbar. Do not combine a mobile drawer and a bottom nav at the same hierarchy level. RTL moves sidebar/drawer origin, overflow alignment, and directional controls logically.

## 12. Login direction

Use one recommended composition: on desktop, a 5/7 split in a restrained full-height frame. The smaller brand panel uses deep ink/mineral background, the product wordmark/name, a one-sentence operational statement, and a subtle abstract ledger-line field; the larger light panel centers a 400–440px authentication form. On tablet/mobile the brand panel becomes a compact top masthead and the form fills the available comfortable width.

The form has a direct title, visible labels, password visibility toggle, password-manager-friendly autocomplete, recovery link, portal link, and a stable inline/global error area. 2FA continues in the same composition and clearly shows progress/back path; recovery uses the same frame. Arabic flips composition and alignment naturally, preserves field order, and uses Arabic typography. Motion is a short initial opacity/translate transition only; authentication is never animated as a marketing experience.

## 13. Workspace/dashboard direction

The workspace is a triage page, not four equal cards. Start with an **Operational priority strip**: personal queue count, SLA-at-risk count, overdue tasks, and a clear “open priority queue” action. Below it, use a wide **My work** list and a companion **Exceptions** rail (SLA risk/overdue/unassigned). Department queue and notifications/tasks follow as evidence-based sections.

Metrics are compact labelled figures with comparator text or target marks—not big number tiles. Reports may use a line for time trends, compact bullet views for target performance, and exception lists for anomalies. Charts only appear where a time/target question exists, include a text/table fallback and local legend, and never replace actionable ticket rows.

## 14. Tickets list direction

Structure: page identity and one create action; queue mode tabs; saved-view/search/filter band; persistent selection/bulk toolbar only after selection; then the table and pagination/status footer. Filters are explicit controls with an applied-filter count and removable values; saved views are named operational presets, not decorative chips.

The table is a professional work plane: reference/subject/requester identity leads; status and priority use restrained labelled indicators; owner, updated time, SLA, and channel follow. Row density is 40px default/36px compact, with a 44px minimum touch target for controls. Sticky column header, sortable controls with accessible state, hover only slightly changes surface, keyboard focus gets a distinct teal ring, selected rows use a low-teal tint plus leading edge, and inline actions appear on hover/focus without causing column jump. Bulk actions are labelled and permission-aware.

At mobile, it transforms into a DataList: ticket identity plus status/priority/SLA on the first line, requester/owner/time in ordered metadata, selection in a stable leading control, and row actions in an accessible menu. Search/filter/saved views remain; low-value columns move to detail rather than forcing horizontal overflow. A deliberate horizontal table viewport is allowed on small tablets only when a DataList would lose critical comparison work.

## 15. Ticket detail workbench direction

At desktop, lead with a single ticket identity/action band: reference, subject, status/priority, owner, SLA countdown, and the one most likely next action. Put the conversation and composer in the broad primary column. Customer identity/context lives in a quieter supporting rail; assignment, status, properties, SLA, watchers, tasks, attachments, history, and AI are organized into contextual sections/drawers, with only current-decision items expanded.

The composer is visually anchored near the conversation, sticky only when it materially helps long threads. Public reply/internal note is a clear mode control with a labelled text distinction and a restrained tinted internal-note region; it does not rely on amber alone. AI is assistive and collapsible, visibly labelled as a suggestion, never competes with the reply action, and preserves human review. Activity/history is chronological, low-emphasis, and expandable. This avoids a twelve-card wall while retaining every current feature and permission gate.

## 16. Forms

Short forms use one open column or balanced two-column desktop grid. Long/admin forms use titled sections with rules, a concise section description, progressive disclosure for advanced configuration, and a sticky save/cancel bar only when the action would otherwise be lost. Modal forms are for focused short tasks; multi-step or high-context work gets a route/page.

Every field has a visible label, required marker with accessible explanation, stable help/error allocation, and server validation next to the related control. On failed submit, show a focusable linked error summary and retain inline errors. Validate normal inputs on blur, not every keystroke; async inputs show loading/no-result/retry distinctly. Read-only differs from disabled. Destructive actions are visually/spatially separated and require contextual confirmation. Bilingual fields show language labels and script-aware direction per field; attachments expose state, progress, scan result, retry, and removal.

## 17. Tables and data display

Headers have medium weight, strong text, and a low-contrast surface/rule; they are not all-uppercase. Use row rules, not zebra striping by default: striping adds noise when status/selection/hover already encode state. Consider a very faint grouping band only in exceptionally long static reports after usability testing.

Numeric columns align to the logical end and use tabular figures; dates/times are locale formatted. Row-level actions are discoverable but subordinate. Empty table regions explain whether the cause is first use, filters, permission, or load failure. Skeletons preserve header/row geometry; partial loading retains current data with a local progress indicator. Responsive behavior follows the V2 DataTable/DataList ADR rather than letting each table invent overflow behavior.

## 18. Status system

Use five semantic families: **neutral** (inactive/draft/unassigned), **informational** (new/in progress/system note), **positive** (resolved/success/healthy), **warning** (at risk/pending review), and **urgent/destructive** (overdue/failed/deleted/blocked). Each uses a quiet background or leading marker, accessible text, and where valuable an icon. Do not map every business status to a unique hue.

Use text plus an icon/marker alone when status is obvious from placement (e.g., a delivery event or inline SLA sentence). Use a compact badge when scanning several rows or a state must remain visible across context. Do not use badges for category, owner, channel, or every piece of metadata.

## 19. Motion personality

CSS-first, transform/opacity-only where motion is needed. Candidate tokens are: hover/press 120–160ms; tooltip/menu/popover 140–180ms; dialog 180–220ms; drawer/sidebar 200–260ms; list content replacement 120–180ms crossfade; skeleton shimmer 1.2–1.6s only for waits beyond roughly one second. Exits are faster than entrances. Use decelerating arrival and accelerating departure curves; no width/height animation and no route-wide reveal choreography.

Respect `prefers-reduced-motion`: render the final state immediately or use an imperceptible opacity change; disable shimmer, drawer travel, and nonessential transitions. Motion cannot block input, reset scroll position, or delay ticket actions.

## 20. Empty, error, and loading states

First-use empty states are compact, contextual teaching moments beside the relevant action. Filtered empties stay within the table/list area and offer clear/reset filters; they are not large centered illustrations. Permission unavailable explains the scope and where to seek access without leaking hidden data. Server errors retain surrounding context and expose retry plus request/support information where appropriate. Offline/network states are persistent but non-blocking, show unsynced/retry status, and never masquerade as “no data.”

Skeletons mirror the real region (table rows, identity strip, conversation messages, property rail). Partial data loading preserves loaded sections and marks only the pending region. Avoid repeating the same centered icon/title/paragraph composition everywhere.

## 21. Staff and portal

Both use the same typography pairs, color semantics, components, focus treatment, icon policy, radii, motion rhythm, and bilingual standards. Staff is denser, has persistent navigation, contextual rails, table-first patterns, and powerful filters/actions. Portal uses wider reading space, fewer simultaneous choices, a simplified header, more explicit help, friendlier empty/error recovery, and comfortable forms. Shared design system does not merge the separate staff cookie and portal bearer authentication models.

## 22. RTL review

Signal Ledger is viable in RTL because its identity comes from rules, hierarchy, and semantic color rather than left-biased decorative composition. Arabic selects IBM Plex Sans Arabic and its dedicated line-height. Sidebar/drawer move to inline-start; active marker, breadcrumb separators/order, filter alignment, menus, dialog close placement, and property rails use logical properties. Directional chevrons/arrows mirror; semantic Lucide icons do not.

Tables retain meaningful column order under RTL rather than blindly reversing business sequence; data/alignment are logical, numeric IDs/dates are bidi-isolated and locale formatted. Status chips remain text-led. Composer public/internal modes remain equally legible; fields support per-value direction where email, IDs, URLs, or Arabic text mix. Arabic forms account for longer labels; dense metadata wraps instead of clipping. RTL visual QA is a release criterion, not translation smoke testing.

## 23. Responsive philosophy

| Width | Behavior |
| --- | --- |
| **1440 desktop** | Persistent 248px sidebar; contextual rails on ticket/customer workbenches; full table columns; sticky headers/action bands; 12-column content grid. |
| **~1024 laptop/landscape tablet** | Sidebar may collapse by user choice; ticket workbench becomes primary column plus collapsible inspector; less-essential table columns hide behind detail; filters wrap into two rows, not tiny controls. |
| **~768 tablet** | Navigation is a drawer; topbar retains page/context/action; ticket inspector becomes a sheet/accordion below primary work; tables choose DataList for task-centric lists; dialogs become near-full-width. |
| **~375 mobile** | One primary task per screen; persistent page action is sticky when essential; search/filter open an accessible drawer; tickets/customers use DataList; ticket conversation and composer remain first, properties/history become sections; portal uses single-column forms and no clipped actions. |

At every size, the ticket subject, status/priority, SLA risk, current assignee, reply action, errors, and current route remain visible or one clear action away. Columns, metadata, and rare actions collapse/move—not the workflow. Touch targets are at least 44px where touch is primary.

## 24. Dark mode decision

**Choose B: build Light + Dark foundations simultaneously, ship Light first.** Semantic token architecture, state contrast, and overlays are too foundational to retrofit safely; token pairs and component contrast tests should be designed alongside V2. The initial POC/release experience is light-only so the visual system can establish its premium mineral identity and avoid doubling page-level implementation/review scope. A user-facing dark-mode release follows only after complete staff/portal semantic, chart, status, and RTL QA.

## 25. V2 DESIGN DO-NOT LIST

1. Do not turn every ticket property into a separate rounded card.
2. Do not use a dark sidebar simply to make the product look “enterprise.”
3. Do not use gradients as a substitute for hierarchy or branding.
4. Do not use glass/frosted panels in normal staff workflows.
5. Do not make all dashboard metrics equally large or equally framed.
6. Do not hide reply, status, assignment, or SLA action behind an unnecessary menu.
7. Do not use a different arbitrary color for each ticket status, category, channel, or admin module.
8. Do not communicate SLA risk with red/amber/green alone.
9. Do not use generic coloured icon squares above every page or card heading.
10. Do not add huge marketing headings to ticket, customer, report, or admin pages.
11. Do not make 16px-radius cards the default component boundary.
12. Do not apply shadows to all panels, rows, inputs, or buttons.
13. Do not introduce raw Tailwind palette/radius/shadow values in V2 feature UI.
14. Do not truncate Arabic labels, ticket subjects, error messages, or security text merely to preserve a row height.
15. Do not treat RTL as `text-align: right`; use logical layout and test bidi values.
16. Do not reverse semantic table columns blindly in Arabic.
17. Do not use text glyphs or mixed icon libraries in place of Lucide.
18. Do not rely on hover-only actions, tooltips, or delivery information.
19. Do not animate loading, route changes, or list rows for decorative effect.
20. Do not use a centered generic empty-state illustration for filtered results or permission states.
21. Do not replace server validation with invented client messages.
22. Do not make a destructive action visually equal to save/send.
23. Do not force dense staff layout onto portal customers.
24. Do not present AI suggestions as authoritative or visually more prominent than the agent’s reply.
25. Do not create a dashboard chart without a question, text summary, and accessible data fallback.
26. Do not use a fake global search/command surface disconnected from ticket/customer/route actions.

## 26. Visual acceptance criteria for the future POC

- A reviewer can identify the product’s visual signature (ledger rules, mineral planes, teal signal, bilingual Plex) without mistaking it for a stock dashboard.
- At 1440px, tickets list, workspace, and workbench reveal primary action/priority in under one visual scan; no wall of equal cards exists.
- Ticket list supports search, filters, saved views, sorting, selection/bulk action, clear status/SLA scan, loading/error/empty states, and accessible DataList transformation.
- Ticket workbench shows identity, SLA, assignment/status, customer context, conversation, composer, history, attachments, and permitted AI features in an intentional hierarchy without twelve equal panels.
- Forms retain labels/help/errors, use a linked error summary after failed submit, distinguish destructive paths, and do not shift layout on validation/loading.
- English LTR and Arabic RTL are reviewed at 1440, 1024, 768, and 375px; no directional bugs, clipped labels, broken table order, or inaccessible menu/dialog behavior remains.
- Normal text/action contrast meets 4.5:1 and focus/non-text UI state meets applicable 3:1 expectations; color is never the sole status signal.
- Keyboard flow, focus restoration, icon names, touch targets, reduced motion, and screen-reader labels are tested for shell, filters, dialog/drawer, table selection, composer, and login.
- Motion is CSS-first, interruptible, transform/opacity based, and imperceptible/reduced under user preference.
- Staff and portal visibly share one identity while portal is calmer and less dense.

## 27. Conceptual token draft

Gate 4 should create semantic, role-based tokens—not raw component hex values. Naming is lowercase dot notation with primitive values private to the foundation and semantic aliases consumed by components.

```text
color.brand.primary | color.brand.accent | color.semantic.success|warning|danger|info
surface.canvas | surface.base | surface.raised | surface.selected | surface.subtle
text.strong | text.default | text.secondary | text.muted | text.inverse | text.link
border.subtle | border.default | border.strong | border.focus
space.0 | space.1 ... space.12
radius.none | radius.sm | radius.md | radius.lg | radius.full
shadow.none | shadow.overlay-sm | shadow.overlay-lg
font.latin | font.arabic | font.size.* | font.lineHeight.* | font.weight.* | font.numeric
motion.duration.* | motion.easing.enter|exit|standard | motion.reduce.*
z.base | z.sticky | z.dropdown | z.drawer | z.dialog | z.toast
density.comfortable.* | density.default.* | density.compact.*
```

Components should consume roles such as `surface.raised` and `text.secondary`, never `#006B5E` or `space.4` directly when a component semantic token is warranted. Direction is not a left/right token family; it is expressed through logical CSS and component behavior.

## 28. UI UX PRO MAX RESEARCH SUMMARY

**Queries performed (non-persistent):**

- `enterprise customer support CRM operations dashboard B2B SaaS` — design-system, density 8/motion 3.
- `premium enterprise software data dense dashboard` — design-system, density 8/motion 3.
- `customer service helpdesk ticketing operations` — design-system, density 8/motion 3.
- `modern enterprise admin workspace` — design-system, density 8/motion 3.
- `Arabic English bilingual RTL enterprise dashboard` — design-system, density 8/motion 3.
- `enterprise operations dashboard` — style.
- `enterprise CRM trustworthy` — color.
- `Arabic bilingual enterprise readable` — typography.
- `data dense table filters selection` — UX.
- `operational dashboard SLA trend queue` — chart.
- `forms validation async errors` — UX.
- `enterprise dashboard table rendering performance` — React stack.

**Relevant returned styles:** Data-Dense Dashboard (compact grid, low-cost effects, sticky headers, filtering, 36px baseline rows), Minimalism & Swiss Style (high contrast, grid, essential hierarchy, minimal effects), and enterprise system examples emphasizing semantic tokens, focus, and restrained depth. These directly informed the dense default, rule-based surfaces, and CSS-first motion.

**Color findings:** The CRM result suggested professional blue/green on a light slate base. V2 retains its trustworthy light-neutral/semantic approach but changes the generic blue into a deeper teal signal and mineral canvas to avoid an interchangeable SaaS palette.

**Typography findings:** The tool surfaced Arabic dedicated families (Noto Naskh/Noto Sans Arabic), plus enterprise options Lexend/Source Sans 3, Plus Jakarta Sans, Inter, and IBM Plex Sans. V2 selects a dedicated IBM Plex Latin/Arabic pair for operational data/scanning; it rejects the Naskh heading direction as too traditional/editorial for a dense CRM and rejects Plus Jakarta Sans continuity as insufficiently Arabic-specific.

**UX findings:** Responsive tables must transform or deliberately scroll; bulk work requires selection and an action bar; validation needs blur timing, retained inline errors, focusable linked error summary, loading buttons, recovery routes, and stable layout. These drive the DataList, filter/bulk toolbar, and form proposals.

**Dashboard/chart findings:** Lines answer time trends; bullets answer compact performance-vs-target questions; anomalies need text/shape annotation and a data-table fallback; avoid excessive series and colour-only differentiation. This informed the exception-first workspace and selective visualizations.

**React guidance:** Profile before optimizing, virtualize lists only when evidence/volume warrants it, and lazy-load route/heavy components. This supports a custom V2 table/DataList POC rather than prematurely adopting a grid/chart library.

**Recommendations rejected:** The generator’s Hero + Features + CTA pattern, sticky conversion CTAs, broad glassmorphism, hero parallax/scroll reveals, generic cobalt/blue CTA approach, Fira Code dashboard heading direction, and dark-mode-by-default framing were rejected. They optimize marketing surfaces or generic templates, not long-session bilingual operations. No generated system was persisted.

## 29. Final recommendation

```text
Recommended design direction: Signal Ledger
Brand personality: Precise, composed, authoritative, calm, lucid, durable, editorial
Design style: Editorial operations desk; open work planes, fine rules, purposeful hierarchy
Color philosophy: Mineral neutral base; scarce deep-teal action signal; restrained semantic status families
Typography: IBM Plex Sans + IBM Plex Sans Arabic, with tabular operational numerals
Density: Dense-but-breathable default; compact tables optional; portal remains more comfortable
Shape language: Mostly 4–8px functional corners; open grouping over card mosaics
Navigation style: Light persistent rail, teal active edge, compact utility topbar, logical RTL drawer
Table style: Flat work plane, sticky header, subtle row rules, measured selection and DataList mobile mode
Form style: Label-led open sections, stable help/errors, linked error summary, progressive disclosure
Motion personality: CSS-first, fast, quiet, orienting, fully reduced-motion safe
Staff/Portal relationship: One identity and component system; staff is operationally dense, portal calm and simpler
Dark mode strategy: Build semantic light/dark foundations now; ship Light first
Primary anti-pattern to avoid: Replacing operational hierarchy with a generic grid of rounded dashboard cards
```

**Gate 3A stop:** Await explicit human approval before any token, CSS, component, shell, route, dependency, or production UI implementation.
