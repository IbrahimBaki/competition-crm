# Frontend V2 — Gate 3B Impeccable Review: Signal Ledger 1.1

**Decision:** Proceed to Gate 4 with the refinements in this review. Signal Ledger is the correct V2 direction because it makes operational judgment—not decoration—the visual centre of gravity. It is sufficiently distinct to be recognizable without a logo, provided its signature is enforced as a compositional system rather than reduced to teal lines on a conventional admin shell.

**Review method:** Impeccable `shape` and `critique` reasoning applied to the approved architecture and Gate 3A proposal. This is a design-planning review only; no UI, tokens, routes, or runtime behaviour are changed. The older `design-system/MASTER.md` describes the incumbent V1 visual system and is intentionally not the V2 visual authority; its accessibility and responsive requirements remain binding.

## 1. Shape review

### Structurally strong

- The information-band model—identity/action band, primary work plane, then a quiet context rail—is a strong replacement for the current panel accumulation. It matches the actual primary workflow: read, decide, respond, then inspect supporting context.
- A light shell with a compact utility bar correctly keeps navigation subordinate to ticket work. The active edge provides a repeatable orientation signal without a dark-sidebar cliché.
- The table-first Ticket List, DataList mobile transformation, and exception-first workspace address real high-frequency work rather than presenting a KPI dashboard.
- Open form sections with rules, label-first controls, persistent error space, and a single form action zone are compatible with long administration forms and bilingual content.
- The portal can share typographic, rule, focus, and color semantics while becoming less dense. That is a credible shared identity, not a forced staff-shell clone.

### Must change before implementation

1. **Specify the decision hierarchy in the workbench.** “Contextual sections/drawers” can become hidden critical work. The always-visible layer must be identity, current status, owner, SLA state, conversation, composer, and the primary permitted action. Customer, properties, tasks, attachments, and activity need explicit priority and access rules below.
2. **Constrain the ledger motif.** Fine rules alone do not create identity; overuse makes a spreadsheet. Limit signature rules to major boundaries and data scanning, not every card, field, message, or menu item.
3. **Correct restricted color usage.** `#B86A22` fails AA for normal text on white and canvas; `#68756F` misses AA on the proposed canvas. Neither may be normal text in the Light implementation.
4. **Make density script-aware.** A 40px default row is appropriate only for one-line, non-wrapping operational records. Arabic subjects, bilingual names, error copy, and message metadata must be allowed to produce a 48–56px row or a two-line DataList rather than being compressed or ellipsized.
5. **Treat saved views and queue modes as navigation, not chip decoration.** They need a defined hierarchy, overflow behaviour, URL state, ownership/sharing state, and permission-aware availability.

### Underspecified

- The exact workbench inspector model: which content is a persistent rail at 1440, a sheet at 1024/768, or a route section at 375.
- The keyboard model for list selection, bulk actions, row opening, command/search, and restoring focus after an action or drawer closes.
- Empty, partial, permission-denied, and stale-data states for every operational band. These must preserve the same hierarchy rather than collapsing into generic centre-page messages.
- The distinction between system activity, customer messages, internal notes, and AI suggestions in a long thread. They need different information roles, not merely different tints.
- A governance map from backend statuses to the five display families. This is essential to prevent a rainbow from reappearing through feature-by-feature choices.

### At risk of becoming generic

- A 248px light sidebar plus 64px top bar becomes a stock enterprise shell if the masthead, group labels, active marker, and page bands do not share one measured rule rhythm.
- Search/command is generic unless it searches actual tickets, customers, queues, and permitted actions from live data; it must not be a decorative omnibox.
- A “priority strip” becomes four KPI cards if counts are boxed equally. It should be one reading band with ordered exceptions and one next-step link.

### Over-designed

- The desktop 5/7 split login proposed in Gate 3A is too familiar and allocates too much visual real estate to non-work content. It risks becoming a SaaS-auth template.
- A persistent teal edge, selected tint, underline, focus ring, active marker, and status marker can all compete. On a given object, use one state marker plus focus when keyboard focus is present.

### Under-designed

- Portal reassurance is described emotionally but needs a concrete composition and information order.
- Arabic optical typography is correctly mentioned but needs scale, line-height, control-height, and bidi rules that a builder can apply consistently.

## 2. Anti-pattern review

Signal Ledger genuinely rejects card grids, glass, gradients, a dark sidebar, icon tiles, radius inflation, and arbitrary motion at the principle level. It will only continue to do so if the following implementation constraints are accepted.

| Anti-pattern | Review | Correction |
| --- | --- | --- |
| Generic SaaS dashboard | Avoided in concept; at risk in shell/workspace. | Make exception lists and work queues the first visual objects. Never lead with equal metric cards. |
| Card grids / card-in-card | Strongly avoided. | A boundary must signal an independent state, action, or temporary layer; otherwise use spacing plus one rule. |
| Excess radius / pills | Strongly avoided. | Reserve full radius for avatars/count dots; use compact rectangular status labels only when scan persistence requires them. |
| Gradients / glass | Avoided. | No gradients. No blur outside an optional modal scrim after contrast testing. |
| Icon tiles | Avoided. | Icons are labels/affordances, not coloured section art. |
| Generic dark sidebar | Avoided. | Keep navigation mineral/light; use ink only for text and high-contrast overlay content. |
| Weak type hierarchy | At risk in “quiet” screens. | Establish clear strong/default/secondary tiers; do not solve density by turning most copy muted. |
| Excess muted grey | At risk. | Muted is tertiary only; identifiers, labels, SLA states, and actionable metadata use strong or secondary text. |
| Arbitrary animation | Avoided. | Motion only explains causality or layer entry; no list choreography, page reveals, or decorative loading. |
| Status rainbow | Avoided in strategy but needs mapping. | Map all backend statuses to five display families; hue alone never differentiates them. |
| Equal visual weight | Avoided in theory. | Every screen names a primary work plane, one primary action, and one exception region; all else steps down. |
| Marketing-like pages | Login remains at risk. | Use the final login composition below: a ledger frame, not a hero. |

## 3. Brand distinctiveness

**Visual Distinctiveness: 9.2/10.**

Without a logo or product name, Signal Ledger remains recognizable when three things recur: a mineral canvas against open white work planes; measured horizontal information bands with a single active edge; and dense bilingual Plex typography with precise, not ornamental, status markers. Its identity is compositional: a support desk that reads like a well-kept operational ledger.

The score would fall below 9 if every page used the same sidebar/topbar/table recipe without the hierarchy of an identity band, an active work plane, and an exception/context region. Do not add novelty. Enforce the rule rhythm, restrained teal allocation, typographic hierarchy, and open-plane composition across login, staff, and portal.

## 4. Signal Ledger 1.1 Candidate Palette

The palette retains Gate 3A’s mineral/teal intent. Contrast ratios below are approximate sRGB ratios against the stated light surface and must be rechecked for every final foreground/background pairing.

| Role | Candidate | Use and restriction |
| --- | --- | --- |
| Canvas | `#F6F7F5` | Page canvas only; never a text fill. |
| Base surface | `#FFFFFF` | Primary work planes, inputs, dialogs. |
| Raised surface | `#FBFCFB` | Menus, hover layers, inspectors; not a default card fill. |
| Primary text | `#16221F` | Titles, body, identity, critical values. |
| Secondary text | `#46534F` | Labels and supporting metadata; AA on white. |
| Muted text | `#65716B` | Tertiary timestamps/hints only; safer than Gate 3A `#68756F` on the mineral canvas. `#68756F` is restricted to white only at 4.81:1 and is **not permitted** on canvas (`4.48:1`). |
| Subtle border | `#DDE3DF` | Rules and boundaries only; not sufficient as a focus indicator. |
| Strong border | `#B9C6BF` | Selected-neutral boundaries and input rest borders; not normal text. |
| Primary | `#006B5E` | Primary actions, links, active edge, selected marker; white text is AA (`6.43:1`). Not a default decorative fill. |
| Primary hover | `#00574D` | Hover/pressed primary actions only. |
| Accent | `#8A531B` | Rare non-semantic editorial highlight, portal reassurance detail, or a non-text marker. Replaces `#B86A22`, which is only `4.10:1` on white and must never be normal text or a text-button fill with white copy. Not CTA, warning, status default, or chart default. |
| Focus | `#007D6E` | 2–3px focus indicator with a light halo; ensure a 3:1 boundary against adjacent surface. Not a selected-state substitute. |
| Success | `#176B4A` | Resolved/healthy semantics with text/icon; not the sole distinction. |
| Warning | `#966100` | At-risk/review semantics with text/icon; not a generic accent. |
| Danger | `#B42318` | Failed, overdue, blocked, destructive; reserve tinted fills for urgent callouts/confirmations. |
| Info | `#1D5E91` | System information and neutral progress; not a second primary brand color. |

Long-session comfort comes from the low-glare canvas, high text contrast, restrained fills, and scarce color—not low contrast. Teal establishes trust and action ownership; the darker clay accent differentiates the system without competing with warning. Semantic colors should generally appear as a leading marker/icon plus text; large saturated surface areas are exceptional.

## 5. Typography review

IBM Plex Sans and IBM Plex Sans Arabic remain the leading pair. They are compact enough for data work, sufficiently human for long messages, and more operationally specific than the incumbent Plus Jakarta Sans. There is no materially stronger reason to replace them before implementation. Validate actual font loading and Arabic rendering in the supported browser set before locking measurements.

### Latin scale

| Role | Size / line-height | Weight |
| --- | --- | --- |
| Page title | 26/32 desktop; 22/28 narrow | 600 |
| Section heading | 18/24 | 600 |
| Band / component heading | 15/20 | 600 |
| UI and body | 14/20 | 400 |
| Label / navigation | 13/18 | 500 |
| Table body | 13/18 default; 14/20 when a subject may wrap | 400 |
| Tertiary metadata | 12/16 | 400 |

### Arabic optical scale

Arabic values are optical, not a mechanical translation of Latin values. Use the same nominal UI minimum where content is comparable, but allow more leading and height:

| Role | Size / line-height | Rule |
| --- | --- | --- |
| Page title | 27/38 desktop; 23/34 narrow | Slightly more open than Latin; do not compress to match a Latin title band. |
| Section heading | 19/30 | Keep at least 6px of vertical breathing room around diacritics. |
| UI/body | 15/24 | Default reading/UI size for Arabic prose. |
| Label/navigation | 14/22 | Labels may wrap before controls shrink. |
| Table/DataList | 14/22 single line; 15/24 when wrapping | Use 48px default record height when Arabic content is present or could arrive. |
| Tertiary metadata | 13/20 | Never reduce critical Arabic metadata below this. |

### Numeric/data treatment

Use IBM Plex Sans with tabular figures for ticket IDs, SLA counters, timestamps, monetary values, queue counts, and aligned columns. Keep number choice and dates locale-aware. Isolate IDs, emails, URLs, phone numbers, dates, and code-like tokens with bidi isolation; do not rely on text alignment to repair mixed direction. SLA counters use strong text and an accompanying semantic marker/text, never a small coloured digit alone.

### Hierarchy and line-height

Headings are sentence case, compact, and sparing: one page title, then bands/sections. Do not create a third marketing-display tier. Latin operational text uses roughly 1.4–1.5 line-height; Arabic UI/prose uses roughly 1.5–1.65. Controls must accommodate the larger script metrics rather than vertically centring Arabic by clipping ascenders/descenders. Long customer messages use comfortable reading measure and 1.6–1.7 line-height in both scripts.

## 6. Ledger motif

The ledger is an information-ordering language, not decoration or a grid background.

| Surface | Where the motif belongs |
| --- | --- |
| Login | One quiet ledger frame: a measured top identity band, form body, and fine divider beneath the authentication context. No illustrated line field or split-panel spectacle. |
| Sidebar | Group labels and group separation rules; one 3px inline-start active edge; a quiet divider before account/help. |
| Workspace | Priority band, list headers, exception boundaries, and selective target baselines. |
| Tickets table | Sticky header rule, row dividers, selected leading marker, and a stable footer/status rule. |
| Ticket workbench | Identity/action band, conversation-to-composer division, and inspector section boundaries. |
| Forms | Section heading plus one rule, field grid alignment, and a clear action boundary. |
| Portal | Header/context divider, ticket summary band, reading sections, and reassuring status timeline boundaries. |

It must **not** appear as graph-paper texture, repeated double borders, a rule around each field, a separator between every message bubble, a background pattern, or a substitute for grouping/whitespace. One or two lines should explain a region; more means the region needs a different composition.

## 7. Shell critique and final shell

The final Staff Shell is a light information index anchored by a slim masthead, grouped route labels, and an active inline-start edge. It has a 248px persistent rail at large desktop, but its true signature is the cadence: masthead → work groups → administrative groups → account/help, each separated by quiet rules rather than cards. The compact utility topbar contains breadcrumb/context, actual global search/command, a single contextual create/action when permitted, notifications, locale, and account controls. It never repeats sidebar navigation.

Breadcrumbs appear only when depth earns them (three or more meaningful levels), use translated labels and logical separators, and collapse middle ancestors rather than overflowing. Notifications open a task-oriented ledger: unread/changed state, timestamp, action, and no decorative feed cards. Account controls group profile, language, security, and sign-out under one labelled menu.

At 1024, a user-controlled compact rail may retain text labels only on hover/focus via a usable affordance; it must not surprise-collapse. At 768 and below, a labelled drawer replaces the rail, opens from inline-start, traps focus, restores focus to its trigger, and leaves the current page title and essential action visible in the topbar. There is no bottom navigation competing with the drawer.

## 8. Final login composition

**Choose: the Ledger Frame.** A full-height mineral canvas with a centred, asymmetric work frame—not a split marketing layout. On desktop, the frame is a wide, quiet 12-column plane: a narrow inline-start identity/context band (about four columns) with product mark/name, one operational promise, and the current auth step; a broad form plane (about five columns) containing the 400–440px form; remaining canvas provides calm negative space rather than imagery. A single vertical rule separates context and work. No hero copy, illustration, gradient, testimonial, or feature list.

On tablet and mobile, it becomes a top identity band followed by an edge-aligned, comfortably wide form plane; the form is not a floating card. Arabic reverses the context/form relationship logically, maintains field order, and uses the Arabic scale above. Login, 2FA, recovery, invitation, loading, and failure all keep the same frame, giving authentication a direct visual connection to the application shell.

## 9. Workspace

The workspace must answer the four operational questions in one scan.

1. **What needs me now?** A top priority band, not cards: ordered items for breached/at-risk SLA, unassigned work in the user’s scope, and overdue tasks. Each item has a count, condition, and direct destination.
2. **What is at risk?** A narrow Exceptions region beside or immediately following My Work, sorted by urgency and including the reason—not just a red count.
3. **What changed?** A compact activity/notification ledger with meaningful change, actor/system source, timestamp, and action link; user-dismissible noise must not outrank risk.
4. **Where should I go next?** A single next-action link in each priority item and a visible My Work queue as the default work plane.

My Work is the broad primary list. Department queues, tasks, and selective trend/target evidence are secondary regions, introduced only when they lead to a decision. Metrics may be inline labelled counts with a comparator, never evenly sized KPI cards. Permissions determine which queues and actions appear; missing permission has a compact explanatory state, not a blank gap.

## 10. Tickets List

**40px remains the default desktop row height** for a single-line, normal-density operational table. It is not an inviolable height: use 48px for wrapped subject/requester content, Arabic rows that need it, selection/bulk mode touch affordances, and any 44px touch target. Compact 36px is an opt-in desktop density for trained users with one-line records only; it is never used on touch-primary views or as an Arabic clipping strategy.

| Visibility level | Contents |
| --- | --- |
| Always visible | Page title; permitted create action; queue mode; search; applied-filter count; table identity (reference, subject, requester); status/priority marker + text; SLA state; pagination/status; keyboard focus. |
| Contextually visible | Saved views; advanced filters; sort state; owner, updated time, channel, customer context; column chooser only if justified; queue-specific explanatory copy. |
| Hidden until selection | Selection count; bulk action bar; destructive bulk action behind confirmation; clear selection. |
| Hidden until hover/focus | Row action menu, secondary shortcut action, row affordance hints. The action cell retains width so no layout jump occurs; all actions remain available by keyboard. |
| Moved to detail on smaller screens | Full properties, channel, secondary timestamps, tags/categories, watchers, long requester context, audit hints, and rare row actions. |

Queue modes are primary operational modes with text labels and count only when count helps triage. Saved views are named presets with ownership/sharing and an overflow menu; they are not a ribbon of pills. Search should accept reference, subject, requester, and defined customer identifiers, disclose its scope, and preserve URL state. Filters open in a compact, labelled bar at desktop and a drawer at mobile; active filters are removable values with a clear-all action.

The table defaults to subject/reference-led scanning. Sortable headers expose current sort/order in text for assistive technology; multi-sort only exists if backend support and visible affordance justify it. SLA displays an ordered condition (for example, “Breach in 18m”) with marker/icon/text, then exact time in supporting metadata. Owner is text/avatar only if identity aids scanning; no avatar gallery. Row selection uses a leading checkbox with range/keyboard support, never click-anywhere selection that conflicts with opening the ticket. Pagination stays visible after long lists and announces loading/results changes without stealing focus.

On mobile, the DataList’s first line is identity plus status/priority/SLA; second line is requester, owner, and changed time in a stable order. Selection stays leading; actions are a labelled menu; filters/search remain reachable. Horizontal table scrolling is allowed only at compact tablet widths where direct comparison genuinely outweighs a DataList, with a sticky identity column and visible scroll affordance.

## 11. Ticket Workbench

The final hierarchy is deliberately unequal:

1. **Ticket identity/action band (always visible at desktop):** reference, subject, status, priority, owner, SLA condition/countdown, and one highest-likelihood permitted action. Status/assignment quick changes remain reachable; destructive/rare actions go to overflow.
2. **Primary work plane:** conversation and composer. The reading sequence is chronological, with system events compressed but expandable. Customer messages, agent replies, and internal notes have text labels and structure, not only color. The composer is anchored after the conversation; when sticky, it must preserve message reading space and never cover content.
3. **Decision inspector:** customer identity, key contact/context, assignment/queue, properties, SLA policy, and tasks. At 1440 this is a narrow, collapsible contextual rail; at 1024 it becomes a collapsible inspector; at 768 it is a labelled sheet/accordion after the primary plane; at 375 it becomes route sections below conversation/composer.
4. **Secondary history/tools:** attachments, activity, watchers, full properties, AI suggestions, and audit-like metadata. These are available, permission-aware, and grouped by purpose, but are not equal panels competing with the reply workflow.

Public reply versus internal note is a labelled segmented mode with text/icon and an explicit recipient consequence. Internal note receives a subtle neutral/informational treatment, never an amber-only cue. Attachments belong to the composer when being added and to a chronological attachment/history region when reviewing; large histories offer type/date filters and pagination or progressive loading. AI is collapsed by default or invoked deliberately, labelled “Suggestion,” cites its input/source when available, and has clear insert/copy/dismiss actions; it never visually outranks Send Reply.

Permission changes remove forbidden actions while retaining harmless explanatory context where useful; a server 403 returns a local action error, not a vanished workflow. On tablet/mobile, actions remain in the identity band and composer stays first; context does not migrate above the conversation. Arabic subjects may take two lines in the identity band, increasing its height rather than truncating critical meaning.

## 12. Table language

Signal Ledger tables are recognizable through a single strong header boundary, disciplined horizontal row rules, high-contrast identity columns, tabular numerical alignment, a thin teal selected marker, and a restrained low-teal selected tint. There are no zebra stripes by default, boxed table cards, or a coloured status column. Header cells are sentence case, medium weight, and never all caps; sticky headers retain an opaque base surface and a shadowless lower rule.

Hover lifts nothing; it makes the row’s action affordance and a slight surface shift perceptible. Focus is stronger than hover. Loading preserves header/row geometry; empty, filtered-empty, permission, and error states retain the table frame and explain the cause locally. The mobile DataList is the same semantic record, not a separate ornamental card list.

## 13. Form language

One V2 form rhythm: page identity → short explanatory sentence where needed → open named sections separated by a rule → fields on a measured grid → local help/error reservation → single action boundary. A short form uses one column or balanced two-column desktop grid; a long configuration form uses section headings and progressive disclosure. It never places each section in a card.

Labels sit above controls, use strong/secondary text, and remain visible while errors appear. Help comes immediately below its field; errors replace neither labels nor help and reserve vertical space where feasible. A failed submit puts a focusable linked error summary at the top and moves focus there once; field-level errors remain authoritative. Inputs are 40px default desktop and at least 44px touch-primary, expanding for Arabic labels/content rather than reducing type. Bilingual fields state the language and allow per-field direction. Read-only is presented as readable content with an explicit state; disabled is reserved for genuinely unavailable input. Destructive controls live in a separated final section/action zone and require contextual confirmation.

## 14. Status language

Use five display families: neutral, informational, positive, warning, and urgent/destructive. Backend taxonomy maps into these families; category, channel, owner, and ordinary metadata do not get status styling.

| Treatment | Use |
| --- | --- |
| Icon + text | Alerts, composer mode, system events, message delivery, destructive confirmations, and any condition requiring immediate interpretation. |
| Marker + text | Dense tables, queue lists, inline SLA state, and timeline entries where repeated scanning matters. |
| Subtle badge | Compact persistent scan target across rows/regions: ticket status, priority, and SLA state only. Rectangular, low-chroma, text-led. |
| Tinted surface | Local exception or confirmation region, selected rows, internal-note body, and urgent attention band. Never every row or all statuses. |
| Plain text | Stable descriptive metadata, categories, channels, owners, dates, and status already unambiguous from a labelled section. |

Every treatment includes text in the current language; semantic color and icon/shape reinforce it. Priority and SLA are distinct concepts: a high-priority ticket is not automatically at SLA risk, so they must not share a single color/badge.

## 15. Portal

The portal uses the same mineral canvas, white open planes, fine rules, Plex pair, teal action/focus signal, and status anatomy—but replaces staff density and navigation power with reassurance and reading order. Its shell is a simple masthead, help navigation, clear account/ticket access, and a compact footer; it has no staff sidebar, queue tabs, dense utilities, or admin inspector.

The portal ticket view begins with a clear ticket summary/status band, then the conversation/updates and a comfortable reply composer. Help and ticket submission use more generous measure and 44px controls. Empty/error states explain what happened, what the customer can do next, and how to obtain help; they do not look like stripped staff-state components. The same ledger motif becomes calm section boundaries and timeline order, not table density. This makes the portal recognizable as the same product while appropriately more readable and reassuring.

## 16. Motion policy

| Interaction | Policy |
| --- | --- |
| Hover / press | 120ms color/opacity; press may use 80ms opacity only. No scale or layout shift. |
| Menus / tooltips / popovers | 140–180ms opacity plus 2–4px transform. Exit 100–140ms. |
| Dialog | 180–220ms opacity plus 6–8px transform; scrim fades. Focus is available immediately. |
| Drawer / sidebar | 200–240ms transform; no width animation. Interruptible and scroll-safe. |
| List updates | Preserve position; 120–160ms local opacity transition only when content changes materially. No entrance cascade. |
| Async states | Immediate determinate/indeterminate feedback; skeleton shimmer only after ~1s, 1.2–1.6s cycle, never on a repeated short operation. |

Use transform and opacity only. Respect reduced motion by rendering final states without travel or shimmer. Motion must never delay typing, selection, reply/send, keyboard navigation, or focus restoration.

## 17. Responsive review

| Width | Conceptual requirement and failure to avoid |
| --- | --- |
| 1440 | Persistent labelled rail; full Ticket List; workbench conversation plus narrow inspector. Avoid three equal columns and an inspector so wide that it starves conversation. |
| 1024 | Keep primary work generous: optional compact rail, inspector collapses, filters wrap in two deliberate rows, less-critical columns move to detail. Avoid a squeezed 1440 layout. |
| 768 | Drawer navigation; DataList for task-centric lists; filters in a controlled drawer/expanded band; inspector sheet/accordion; near-full dialogs. Avoid tiny wrapped toolbars and a two-column workbench. |
| 375 | One primary task; sticky essential action only; ticket identity/conversation/composer first; properties/history in sections; full-width form controls. Avoid horizontal action strips, clipped Arabic text, and “mobile cards” that hide operational meaning. |

Bulk actions become a sticky, labelled selection bar at 768/375 with a clear close/deselect affordance and no overlap with the composer. On 375, ticket filters use a modal drawer with applied count and reset/apply actions; pagination remains explicit. Forms collapse to one column and preserve error summary. At all widths, current route, current assignee, status/priority, SLA risk, and next action are visible or one intentional action away.

## 18. RTL design review

Arabic must be a native information system, not mirrored English. Use logical inline/block properties, but make content decisions deliberately:

- Keep the semantic order of ticket columns based on scanning task, not a blind visual reversal. Identity remains first in the reader’s scan sequence; numeric/time columns stay consistently grouped and logically aligned.
- Wrap IDs, emails, URLs, phone numbers, date strings, and SLA timers in bidi isolation. Ticket references should retain a stable LTR value direction even in an RTL row.
- Allow Arabic page titles, subjects, labels, and validation messages more vertical room. Never shrink them to retain a Latin-engineered 40px row or a one-line toolbar.
- Put the active edge, rail/drawer origin, popover alignment, close/control placement, and overflow menus at inline-start/end according to purpose. Mirror directional chevrons; do not mirror semantic icons.
- In breadcrumbs, retain logical hierarchy and localize separators; do not reverse the data model merely because the page is RTL.
- In forms, align label/control and error reading order to Arabic; bilingual field labels clearly name the script and inputs choose their own value direction. Email/URL inputs remain LTR even in Arabic forms.
- Composer mode, recipient consequence, attachment actions, and send direction must be explicitly labelled. Do not rely on a mirrored arrow to distinguish public reply from internal note.

RTL visual QA must include long Arabic names, multi-line subjects, Arabic plus Latin IDs, numerical SLAs, mixed email addresses, filtered empty states, drawer/menu focus order, and table/DataList selection at all four breakpoints.

## 19. Signal Ledger 1.1 — final refined direction

**Brand personality:** precise, composed, calm, durable, and service-minded. It is authoritative without severity and editorial without becoming decorative.

**Visual signature:** mineral canvas; open white work planes; a measured cadence of horizontal rules; high-contrast bilingual Plex; a single scarce teal signal; thin active/selected markers; and a hierarchy of identity band, work plane, and decision inspector.

**Palette:** the Signal Ledger 1.1 Candidate Palette above. Color is semantic and scarce; the clay accent is restricted, and normal-text contrast is non-negotiable.

**Typography:** IBM Plex Sans plus IBM Plex Sans Arabic. Script-aware optical scales, generous Arabic leading, tabular numeric treatment, and bidi-safe operational values make density safe rather than merely compact.

**Density and shape:** default dense-but-breathable. 40px one-line desktop table rows, 48px when content warrants; optional 36px compact only for suitable desktop data. Corners are 4–8px functional, with open groups preferred to cards. Flat planes and rules are default; elevation belongs to transient layers and focused sticky work.

**Ledger motif:** a compositional rule language for major bands, lists, headers, and section boundaries—not a decorative grid.

**Application shell:** light persistent information rail, compact utility bar, real command/search, grouped navigation, one active edge, measured breadcrumbs, and an accessible logical mobile drawer.

**Login:** Ledger Frame: context band and work form within one quiet frame, responsive into masthead plus form; no split hero or marketing presentation.

**Workspace:** triage-first priority band, broad My Work list, focused Exceptions region, then queues/tasks/change context. Counts prove a condition; they do not create a KPI wall.

**Ticket List:** queue mode, saved views, search and filters lead into a flat data work plane. The list has persistent identity/status/SLA scan, permission-aware selection/bulk actions, robust keyboard behaviour, and a first-class mobile DataList.

**Ticket Workbench:** identity/action band → conversation/composer primary plane → collapsible decision inspector → secondary history/tools. AI remains assistive; context never outranks responding.

**Tables:** sticky strong header, subtle rules, no zebra by default, selected tint plus marker, actions on hover/focus without reflow, and semantic DataList transformation.

**Forms:** label-led open sections, stable help/errors, linked error summary, bilingual field direction, progressive disclosure, and a deliberate action/destructive boundary.

**Status system:** five semantic families with text plus marker/icon; badges only where scan persistence is needed. No category/channel rainbow.

**Motion:** CSS-first, quiet, fast, interruptible, reduced-motion safe, and causally meaningful.

**Staff/Portal relationship:** one visual foundation and accessibility model. Staff optimizes scanning/action density; portal optimizes reassurance, reading, and recovery.

**Responsive:** preserve task hierarchy at 1440/1024/768/375 by moving supporting content, not by shrinking or hiding critical workflow.

**RTL:** script-aware type and height, logical layout, intentional content order, bidi isolation, and direction-aware interaction—not a right-aligned LTR shell.

**Dark-ready strategy:** **Light-first implementation, dark-ready semantic architecture.** Gate 4 creates role-based semantic token names and component roles that can resolve to a future dark theme. It does not create a full dark palette, dark-mode UI, dual visual review scope, or component-specific dark overrides now. Components must never encode light hex values or assume a white surface; contrast pairs and elevation semantics must be testable when a future dark theme is introduced.

## 20. Changelog from Gate 3A

| Area | Gate 3A | Signal Ledger 1.1 | Reason |
| ---- | ------- | ----------------- | ------ |
| Login | 5/7 desktop split with brand panel and abstract ledger-line field | Ledger Frame: one asymmetric work frame with context band, form plane, and negative space | Avoids a generic SaaS auth split while tying auth to application composition. |
| Muted text | `#68756F` tertiary metadata | `#65716B` default muted; Gate 3A value restricted to white only and not canvas | `#68756F` is 4.48:1 on canvas, below AA. |
| Accent | `#B86A22` rare editorial accent | `#8A531B`, same restricted role | Gate 3A accent is 4.10:1 on white and cannot safely serve as normal text. |
| Dark strategy | Build light and dark foundations simultaneously; ship Light first | Dark-ready semantic architecture; Light-first implementation only | Meets the corrected scope without creating premature dark visual work. |
| Arabic sizing | Script-specific line height proposed | Explicit Arabic optical scale, control/row expansion, and bidi rules | Prevents Latin geometry from compressing Arabic. |
| Table density | 40px default / 36px compact | 40px conditionally; 48px for wrapped/Arabic/touch records, 36px opt-in desktop-only | Preserves density without clipping real bilingual content. |
| Workbench hierarchy | Contextual sections/drawers described broadly | Four-tier visibility hierarchy with breakpoint migration | Prevents equal-panel clutter and hidden critical information. |
| Ledger motif | Fine rules/information bands proposed | Placement and prohibition map | Protects identity without producing a spreadsheet effect. |

## 21. Design confidence

| Area | /10 |
| ----------------------- | --: |
| Visual identity | 9.2 |
| Enterprise credibility | 9.5 |
| Long-session usability | 9.3 |
| Data density | 9.2 |
| Ticket workflow | 9.1 |
| Arabic RTL | 9.0 |
| Accessibility potential | 9.2 |
| Portal adaptability | 9.0 |
| Maintainability | 9.3 |
| Avoids generic SaaS | 9.1 |

**Overall Design Confidence: 9.2/10**

Gate 4 should proceed. The implementation brief must carry forward the non-negotiables in this review: contrast restrictions, script-aware type and density, the defined workbench hierarchy, a governed status mapping, scoped ledger rules, real command/search integration, and RTL acceptance data. These are refinement constraints, not a replacement architecture or a new direction.
