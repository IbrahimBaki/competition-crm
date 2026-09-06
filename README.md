# Customer Support CRM

## 1. Project Overview

Customer Support CRM is a bilingual, multi-branch and multi-department support platform for support staff, managers, administrators, customers, and public visitors. It addresses the full service workflow: identifying customers, receiving requests from multiple channels, working tickets, meeting SLA targets, sharing knowledge, communicating with customers, reporting operations, and securely administering the system.

The application is a React 18 single-page application (SPA) over a Laravel 12 API and MySQL database. The frontend uses TypeScript strict mode, React Router, TanStack Query, i18next, Radix-based accessible components, and a reusable design system. The backend uses Laravel controllers, form requests, policies, actions/services, queued jobs, scheduled commands, Sanctum authentication, and an OpenAPI v1 contract. The API is exposed under /api/v1 and separates public, portal, and staff boundaries.

| Concern | Implementation |
|---|---|
| Frontend | frontend/src/router.tsx; frontend/src/features; frontend/src/design-system; frontend/package.json |
| Backend | routes/api.php; bootstrap/app.php; app/Domains |
| Data | 104 migrations in database/migrations; UUID generation in app/Support/Models/GeneratesUuid.php |
| Authentication | Laravel Sanctum stateful staff session; dedicated portal guard |
| Async and queue architecture | domain jobs for messaging, inbound email, webhooks, imports, and report exports |
| Scheduler | routes/console.php schedules backup, retention, SLA, automation, reporting, email, chat, and task work |
| API model | docs/api/openapi.yaml; frozen v1 contract at docs/api/openapi.v1.frozen.yaml |
| Integration model | configurable adapters, API tokens, signed outbound webhooks, ERP context, provider transports |

The repository uses a domain-oriented design: app/Domains/Ai, Automation, Channels, CustomerPortal, Customers, Integrations, Knowledge, Notifications, Organisation, Portal, Reporting, Security, Sla, Ticketing, and Workspace. This structure is the primary Engineering Foundations boundary for product behaviour, security, data access, jobs, and tests.

## 2. Original Requirements / Product Scope

The original Customer Support CRM specification is represented directly by these implemented product areas.

1. **Customer Management** — customer profiles, contact details, interaction history/timeline, notes, attachments, identity resolution, duplicate handling, and customer merge.
2. **Ticket Management** — create and track tickets, categories, custom category fields, assignment, status lifecycle, escalation, history, messages, merge/split, watchers, links, queues, and saved views.
3. **Communication Channels** — inbound email, WhatsApp, live chat, SMS, public web forms, delivery receipts, templates, and replay/processing workflows.
4. **Agent Dashboard / Workbench** — queues, assigned tickets, customer context, tasks/reminders, quick replies, notifications, watchers, history, SLA state, and AI assistance.
5. **SLA & Automation** — response/resolution targets, working hours, pauses, breaches, routing/automatic assignment support, escalation actions, rule executions, alerts, and notifications.
6. **Knowledge Base** — FAQs/help articles, categories, visibility, versions, guides/solutions, full search, customer access, and feedback.
7. **AI Features** — ticket summaries, suggested replies, automatic categorization, knowledge/suggested solutions, chatbot integration, usage tracking, privacy sanitisation, and approval controls.
8. **Customer Portal** — registration/verification/login, submit tickets, track requests, view owned history/conversation, access FAQs, downloads, and submit feedback.
9. **Reports & Management** — report catalogue, management dashboard, filters, exports, scheduled reports, SLA and ticket data, agent/customer-focused reporting capabilities.
10. **Security & Administration** — users, roles, permission keys, RBAC scope, policies, audit logs, 2FA, authentication policy, data protection, branches/departments/teams, and configuration.
11. **Integrations** — versioned API, API tokens, ERP customer context, email/SMS/WhatsApp provider boundaries, webhooks, imports, and external systems.
12. **Platform** — Arabic and English, RTL/LTR, responsive/mobile-friendly UI, multi-department, multi-branch, and design-system branding tokens.

The source specification and detailed UI module breakdown remain at docs/ui/00-overview.md through docs/ui/13-public-surfaces.md; this README contains the evaluator-facing summary and evidence directly.

## 3. Requirements Coverage & Evidence Matrix

| Requirement | Coverage | Implementation evidence | Verification evidence |
|---|---|---|---|
| Customer profiles and contact details | Implemented | app/Domains/Customers; CustomerController.php; CustomerContactController.php; frontend/src/features/customers | tests/Feature/Customers; PhoneNormalisationTest.php; CustomerIdentityResolverTest.php |
| Interaction history, notes, attachments | Implemented | CustomerTimelineController.php; CustomerNoteController.php; CustomerAttachmentController.php; CustomerEvent model | CustomerTimeline.test.tsx; AttachmentRulesTest.php |
| Ticket create and track | Implemented | TicketController.php; StoreTicketRequest.php; frontend/src/pages/operations/CreationPages.tsx | tests/Feature/Ticketing; TicketReferenceGeneratorTest.php |
| Categories, fields, priority/status lifecycle | Implemented | TicketCategoryController.php; TicketStatusController.php; TicketLifecycleController.php; TicketTransitionMapTest.php | TicketCategoryTreeTest.php; TicketStatusLifecycleTypeTest.php |
| Assignment, queues, collaboration | Implemented | TicketAssignmentController.php; TicketQueueController.php; TicketWatcherController.php; workspace features | TicketAssignmentTest.php; workspace tests |
| Ticket history and escalation | Implemented / automated | TicketController history; RecordTicketEvent.php; TicketEscalationController.php | Ticket concurrency, lifecycle and SLA test inventory |
| Email | Integrated | Channels/Email; InboundEmailWebhookController.php; ProcessInboundEmailJob.php | EmailCorrelatorTest.php; InboundClassifierTest.php |
| WhatsApp and SMS | Configurable integration | Channels/Messaging; ProviderInboundWebhookController.php; SendProviderMessageJob.php; config/channels.php | messaging/domain tests and webhook/provider configuration |
| Live chat and web forms | Integrated | Channels/Chat; Channels/WebForm; PublicChatSessionController.php; PublicWebFormController.php | WebFormPayloadValidatorTest.php; Channel feature tests; Playwright inventory |
| Agent dashboard, tasks, quick replies | Implemented | Workspace domain; AgentTaskController.php; QuickReplyController.php; frontend/src/features/workspace | Workspace feature tests; WorkspaceV2.test.tsx |
| SLA response/resolution, pauses, breaches | Automated | SlaPolicy, TicketSlaClock, SlaBreach, SlaClockService.php | SlaClockServiceTest.php; NoDirectTimeArithmeticTest.php |
| Automation and routing/escalation | Automated | AutomationRuleController.php; Services/Conditions/ConditionEvaluator.php; Services/Routing/AutoAssignTicket.php; AutomationSweepCommand.php | RuleEngineOrderingTest.php; ConditionEvaluatorTest.php; AutomationSmokeTest.php |
| Knowledge Base and search | Implemented | Knowledge domain; SearchArticles.php; public knowledge routes | Knowledge visibility/versioning/feedback feature tests |
| Ticket summaries and suggested replies | Configurable | Ai actions GenerateTicketSummary.php and GenerateSuggestedReply.php | Ai feature-test inventory; feature-gate/provider controls |
| Categorization, solutions, chatbot | Configurable | ClassifyTicket.php; SuggestKnowledgeArticles.php; ChatbotAnswerer.php; ChatbotHandoffPolicy.php | AI feature tests; sanitisation/budget code paths |
| Portal submit, track, history, feedback | Implemented | PortalTicketController.php; PortalTicketMessageController.php; GuestTicketTrackingController.php; frontend/src/pages/portal | Portal feature tests; portal-isolation.test.ts |
| Reports, dashboard, export, schedules | Automated | Reporting domain; ReportController.php; GenerateReportExportJob.php; ReportScheduleSweepCommand.php | Reporting tests; historical performance budget evidence |
| Users, RBAC, audit logs | Implemented | Security domain; PermissionKey.php; policies; AuditLogger.php | Security feature/unit test inventory |
| APIs, ERP, webhooks, import | Configurable | Integrations domain; CustomerErpContextController.php; WebhookSigner.php; RunImportJob.php | ApiTokenLifecycleTest.php; integrations feature tests |
| Arabic, English, RTL/LTR, responsive | Implemented | frontend/src/i18n/ar.json; en.json; LocaleProvider.tsx; design system | components.test.tsx axe/RTL checks; responsive.spec.ts |
| Multi-branch, multi-department, branding | Implemented | Organisation domain; Branch/Department/Team controllers; design-system/MASTER.md | Organisation tests; frontend organisation tests |

## 4. Planning & Task Breakdown

Planning is preserved in FRONTEND_REBUILD_EXECUTION.md, docs/ui/00-overview.md through docs/ui/13-public-surfaces.md, docs/contracts, and Git delivery milestones. The following repository-derived implementation breakdown makes the plan and dependencies visible without requiring those files.

| Workstream | Objective and key tasks | Dependencies / artifacts | Verification strategy |
|---|---|---|---|
| Foundation | Establish API envelope, OpenAPI freeze, design tokens, error/loading states, typed client | docs/api/openapi.yaml; frontend/src/api; frontend/src/design-system | API contract lint/generation; component/API tests |
| Authentication/RBAC | Staff/portal identity, session handling, 2FA, permission scope and audit | Security domain, Sanctum config, policies, auth routes | Security tests, permission/session frontend tests |
| Organisation | Branch, department, team, placement, calendars | Organisation controllers, working-hour/holiday migrations | Organisation feature/unit tests |
| Customer Management | Profiles, contacts, notes, files, timeline, identity, duplicates | Customers models/actions/controllers and React features | Customer feature/unit/component tests |
| Ticketing and Workbench | Queues, ticket lifecycle, messages, assignment, watchers, links, customer context | Ticketing domain and frontend ticket features | Ticket unit/feature tests and ticket-workbench E2E specification |
| SLA and Automation | Clocks, targets, pauses, breaches, rules, routing/escalation sweeps | Sla/Automation domains; routes/console.php | SLA/automation rules tests |
| Communication | Email/webhook ingestion, provider adapters, forms/chat, delivery state | Channels jobs/controllers/configuration | Channel and payload-validator tests |
| Knowledge and AI | Article lifecycle/search/public help, AI assist gate/provider/budget/review | Knowledge/Ai domains; config/ai.php | Knowledge/AI tests |
| Portal | Account verification, customer-scoped ticket conversation, feedback, guest tracking | Portal domain, portal routes/pages | Portal auth/visibility/isolation tests |
| Reports/Administration/Integrations | Reporting/export/schedules, roles, audit, tokens, webhooks, ERP/import | Reporting, Security, Integrations domains | Reporting/integration/security tests |
| Testing and Delivery | Unit/feature/E2E, lint/type/build, migrations, vhosts, queues/scheduler/backups | Makefile; package scripts; Docker/config files | Reproducible commands in sections 19, 20, and 28 |

## 5. Architecture & Engineering Foundations

### Architecture

    React SPA
      -> React Router routes: frontend/src/router.tsx
      -> TanStack Query/API boundary: frontend/src/api and feature hooks
      -> /api/v1 OpenAPI contract
      -> Laravel controllers + Form Requests + Policies
      -> Domain Actions/Services/Models
      -> MySQL migrations/tables, queues/jobs, notifications, storage

Staff routes in routes/api.php use auth:sanctum and portal.deny. Portal routes use auth:portal and portal.auth. Public channel/knowledge endpoints have dedicated public protection and throttles. This prevents public or portal credentials from being treated as staff access.

### Engineering foundations

- **Routing and API:** routes/api.php is the authoritative versioned API route map; frontend/src/router.tsx maps staff, portal, and public SPA routes. docs/api/openapi.yaml is the typed contract and docs/contracts/api-contract-freeze.md defines additive v1 contract maintenance.
- **Authentication/session handling:** AuthController.php, AuthMeController.php, AuthenticateStaff.php, config/auth.php, config/session.php, config/sanctum.php, and frontend/src/auth/AuthProvider.tsx implement staff identity/session lifecycle. PortalAuthController.php and frontend/src/portal/auth provide a separate customer model.
- **Authorization/RBAC:** PermissionKey.php, Scope.php, ResolveEffectiveScope.php, domain Policies directories, ProtectedRoute.tsx, RequirePermission.tsx, and ActionGuard.tsx provide server enforcement and UI gating.
- **Service/action domain layers:** app/Domains contains Actions, Services, Http, Models, Policies, and Events/Jobs rather than concentrating business logic in controllers.
- **Jobs, scheduler, notifications:** ProcessInboundEmailJob.php, SendProviderMessageJob.php, DeliverWebhookJob.php, RunImportJob.php, GenerateReportExportJob.php, and DispatchNotificationsListener.php perform deferred work. routes/console.php schedules recurring maintenance without overlap.
- **Configuration, storage, search and error handling:** config/channels.php, ai.php, integrations.php, reporting.php, security.php, retention.php, filesystems.php, cors.php; attachment rules/storage are in app/Support/Http/Uploads; knowledge search uses SearchArticles.php and ArticleSearchIndexer.php. Unified API error/envelope handling is tested in frontend/src/api/http/__tests__.
- **Localization and validation:** Form Request classes under each domain enforce server validation; i18next catalogs and LocaleProvider.tsx maintain Arabic/English language and RTL/LTR direction.

## 6. Database & Data Model

The Laravel data model is represented by 104 ordered migrations and UUID-based domain models. Major entities and relationships include:

| Area | Entities and evidence |
|---|---|
| Organisation/RBAC | users, roles, role_permissions, user_role, branches, departments, teams, user_branch, user_department; migrations 2026_08_26_010100 through 2026_08_26_030600; Security/Organisation models |
| Customers | company_accounts, customers, customer_contacts, customer_notes, customer_events, duplicate candidates; 2026_08_26_080100 through 2026_08_26_090300; Customers models |
| Ticket data | tickets, ticket_references, statuses, categories, category_fields, tags, saved_views, events, messages, message_delivery_events, watchers, links; 2026_08_26_100100 through 2026_08_27_180100; Ticketing models |
| SLA and automation | sla_policies, sla_targets, ticket_sla_clocks, sla_pause_intervals, sla_breaches, automation_rules, automation_rule_executions; 2026_08_26_140100 through 2026_08_26_150400 |
| Knowledge/AI | knowledge_categories, knowledge_articles, versions, feedback; ai_suggestions, ai_usage_records, chatbot_turns; 2026_08_28_010100 through 2026_08_28_020500 |
| Portal/channels | portal_accounts, verification tokens, guest grants, feedback; web_forms/fields/submissions, chat sessions/messages/events, inbound email and provider message data |
| Integration/operations | api_tokens, webhook_subscriptions/deliveries, import_runs/rows, report_exports/schedules, notifications/delivery attempts, audit_logs, idempotency_keys |

Ticket records link customer context, category/status and assignment, while TicketEvent and customer events preserve append-oriented history. TicketSlaClock, pause intervals, targets and breaches represent SLA lifecycle separately from interactive HTTP requests. Audit logs, webhook deliveries, report exports, import runs, notification attempts, AI suggestions/usage, and portal grants preserve operational state required for administration and reporting.

## 7. End-to-End Workflow Evidence

### Customer lifecycle

Customer UI (frontend/src/features/customers and CustomersPage/CustomerDetailPage) → React query/API mapping → GET/POST/PATCH /api/v1/customers routes in routes/api.php → CustomerController.php and domain Form Requests → CustomerPolicy.php → customer/contact/note/event/attachment tables → API resource envelope → TanStack Query refresh and visible timeline. Contact, note, attachment, timeline, duplicate, block, and merge endpoints are explicit in routes/api.php. Customer identity and phone normalisation are exercised by CustomerIdentityResolverTest.php and PhoneNormalisationTest.php.

### Ticket lifecycle

Ticket UI (frontend/src/features/tickets, TicketDetailPage.tsx, creation page) → ticket queue/create/show routes → StoreTicketRequest.php and TicketController.php → Ticketing actions/services/models → tickets, events, messages, status, assignment, watcher, link and SLA tables → TicketResource/API envelope → query invalidation/UI state. Assignment flows use TicketAssignmentController.php; status changes use TicketLifecycleController.php and TicketTransitionMap; history uses TicketController history and RecordTicketEvent.php. Version guards provide conflict state on stale updates. TicketSlaClock and scheduled sla:sweep calculate SLA state; notifications and automation consume workflow events.

### Agent workbench, SLA and automation

Agent workspace/queue UI → AgentTaskController.php, QuickReplyController.php, ticket queue endpoints, notification endpoints → AgentTask/QuickReply/ticket models and AgentTaskEvent → reminders via AgentTaskReminderSweepCommand.php. SLA policy and clock routes → SlaClockService.php and working-time services → SLA clocks/pauses/breaches → sla:sweep every five minutes. AutomationRuleController.php → rule condition/action services and execution records → automation:sweep every ten minutes; TicketEscalationController.php exposes escalation as a workflow action.

### Communication, portal, knowledge, AI, and reporting

- **Communication:** inbound email/provider webhook/public form/chat route → validation/public protection → channel controller → queued ProcessInboundEmailJob or messaging jobs when applicable → ticket/message/session/submission state → staff or visitor UI.
- **Portal:** portal page → PortalProtectedRoute/portal auth boundary → portal API → PortalTicketController/PortalTicketMessageController plus visibility services → only customer-owned ticket/messages/attachments → portal React update. Guest tracking uses GuestTicketTrackingController.php and token route.
- **Knowledge:** staff article page → KnowledgeArticleController.php and policy/request → article/category/version tables → public knowledge routes/search → PortalHelpPage and article page. Search uses SearchArticles.php.
- **AI-assisted workflow:** ticket assist route → TicketAiAssistController.php → feature gate, payload sanitiser, budget guard and AiProvider interface → persisted suggestion/usage record → human resolution/approval. PostTicketMessage.php rejects unapproved AI-generated customer content.
- **Reporting/export:** report UI → ReportController.php/query validation → reporting data → export request → GenerateReportExportJob.php/report export record → scheduled report sweep or download workflow.

## 8. Customer Management

Customer Management is implemented in app/Domains/Customers and frontend/src/features/customers. CustomerController.php provides the core resource; CustomerContactController.php, CustomerNoteController.php, CustomerAttachmentController.php, CustomerTimelineController.php, CustomerDuplicateController.php, CustomerMergeController.php, and CustomerBlockController.php implement specialised behaviour. Models/migrations include CompanyAccount, Customer, CustomerContact, CustomerNote, CustomerEvent, and duplicate candidates.

Operationally, authorised staff create and maintain profiles and contacts, record private notes, upload/download controlled attachments, inspect interaction history/timelines, resolve identity, review duplicates, and merge records. CustomerPolicy.php and request classes enforce access and validation. Evidence includes tests/Feature/Customers, tests/Unit/Customers, CustomerTimeline.test.tsx, CustomerDangerActions.test.tsx, MergePreviewDialog.test.tsx, and customer API/query tests.

## 9. Ticket Management

Ticket Management is implemented in app/Domains/Ticketing and frontend/src/features/tickets. routes/api.php exposes create/list/show/update, queues, history, status/reopen/spam, merge/split, assign/unassign/claim/transfer, links, messages/delivery/retry, watchers, categories and saved views.

Key code includes TicketController.php, TicketLifecycleController.php, TicketAssignmentController.php, TicketMessageController.php, TicketMergeController.php, TicketWatcherController.php, RecordTicketEvent.php, StoreTicketRequest.php, TicketPolicy.php, TicketVersionGuardTest.php, TicketTransitionMapTest.php, TicketReferenceGeneratorTest.php, DeliveryStateMachineTest.php, and TicketAssignmentTest.php. Advanced capabilities include queues, saved views, watchers, links, tags, delivery events, customer-visible/internal message handling, merge/split, and optimistic concurrency.

## 10. Communication Channels

| Channel | Architecture and evidence |
|---|---|
| Email | InboundEmailWebhookController.php accepts ingress; ProcessInboundEmailJob.php processes; InboundEmailReplayController.php supports operations; correlator/classifier/quoted-text tests cover message handling. |
| WhatsApp | ProviderInboundWebhookController.php and ProviderDeliveryReceiptController.php receive provider callbacks; ProcessProviderInboundMessageJob.php and SendProviderMessageJob.php implement background processing; transport/secret/window policy is configured in config/channels.php. |
| SMS | The same configurable messaging provider boundary exposes SMS inbound/receipt routes, delivery job and SMS limits/configuration. |
| Live Chat | PublicChatSessionController.php and staff ChatSessionController.php manage sessions/messages/end/transfer; ChatSessionSweepCommand.php manages recurring session work; frontend public chat route is /chat. |
| Web Forms | PublicWebFormController.php supports protected public form/show/submission/tracking; WebFormController.php manages staff-side definitions; form fields/submissions are persisted and payload validation has unit coverage. |

Email, WhatsApp, and SMS are implemented through application provider/webhook boundaries. Provider-side transport requires deployment credentials configured in docker/env-additions.txt and config/channels.php; the webhook, validation, delivery-state, retry, queue, and configuration architecture is in the repository.

## 11. Agent Dashboard / Workbench

The staff workspace is available through frontend/src/pages/WorkspacePage.tsx and frontend/src/features/workspace. It combines queue context, assigned/scope-visible tickets, SLA-risk work, agent tasks, reminders, quick replies, notifications, and staff navigation. Ticket detail provides customer information, conversation, internal/public visibility, transition controls, assignment, watchers, links, history, SLA panel, and AI assist endpoints.

Backend evidence is app/Domains/Workspace, AgentTaskController.php, AgentTaskStateController.php, QuickReplyController.php, QuickReplyRenderController.php, NotificationController.php, NotificationPreferenceController.php, and DispatchNotificationsListener.php. Collaboration is reinforced by ticket watchers, assignments/transfers, event history, mentions/delivery records, and team/department scoping. Tests include WorkspaceV2.test.tsx, useSlaRiskTickets.test.ts, notificationTarget.test.ts, and feature tests under tests/Feature/Workspace.

## 12. SLA & Automation

SLA policy management is implemented by SlaPolicyController.php, TicketSlaController.php, ResetTicketSla.php, SlaClockService.php, WorkingTimeService.php, and models SlaPolicy, SlaTarget, TicketSlaClock, SlaPauseInterval, and SlaBreach. This represents response/resolution targets, business working hours, pausing, breach state, and reset behaviour. routes/console.php runs sla:sweep every five minutes, keeping time calculation outside ordinary user requests.

Automation is implemented by AutomationRuleController.php, AutomationRuleExecutionController.php, TicketEscalationController.php, AutomationSweepCommand.php, Services/Conditions/ConditionEvaluator.php, Services/RuleEngine.php, and automation_rules plus automation_rule_executions tables. Services/Routing/AutoAssignTicket.php and round-robin, least-busy, skill-based, and manual strategies implement routing/automatic assignment. Escalation is a dedicated ticket action. Notifications and agent tasks provide the alert/reminder layer. Test evidence includes SlaClockServiceTest.php, NoDirectTimeArithmeticTest.php, RuleEngineOrderingTest.php, ConditionEvaluatorTest.php, AutomationSmokeTest.php, and reporting tests that protect SLA calculations.

## 13. Knowledge Base

The Knowledge Base supports FAQ/help articles, guides and solutions through app/Domains/Knowledge. KnowledgeArticleController.php, KnowledgeCategoryController.php, KnowledgeArticleStateController.php, KnowledgeArticleVersionController.php, KnowledgeArticleSearchController.php, KnowledgeArticleRenderController.php, and KnowledgeArticleFeedbackController.php implement authoring, category organisation, lifecycle/visibility, version restore, rendering, search, and feedback.

SearchArticles.php and ArticleSearchIndexer.php provide search/indexing. ArticleAudienceResolver.php and ArticleQueryScope.php ensure audience-safe content selection. Staff routes are permission-protected; routes/api.php separately exposes public knowledge categories, list, search, article, and feedback for portal/customer help. Tests/Feature/Knowledge covers categories, feedback, versions, and visibility; ArticleTransitionMapTest.php covers lifecycle rules.

## 14. AI Features

Product AI is implemented as a configurable assistance boundary in app/Domains/Ai. TicketAiAssistController.php calls GenerateTicketSummary.php and GenerateSuggestedReply.php; ClassifyTicket.php supports automatic categorization; SuggestKnowledgeArticles.php supports suggested solutions/knowledge recommendation; ChatbotAnswerer.php, ChatbotIntegration.php, and ChatbotHandoffPolicy.php support the chatbot integration and handoff model.

AI architecture uses AiProvider.php, AiClient.php, AiFeatureGate.php, AiPayloadSanitiser.php, AiBudgetGuard.php, RecordAiUsage.php, and models AiSuggestion, AiUsageRecord, and ChatbotTurn. config/ai.php and environment variables control enablement, provider, feature flags, timeout, monthly budget, and personal-data redaction. Failure boundaries include NullAiProvider.php, AiProviderUnavailableException.php, AiFeatureDisabledException.php, and AiBudgetExceededException.php. ResolveAiSuggestion.php provides human resolution; PostTicketMessage.php requires explicit approval before AI-generated content can be sent externally.

## 15. AI Usage & Human Verification

This section concerns AI-assisted development verification, distinct from the product AI features above. The repository provides evidence that generated or AI-assisted implementation is not treated as correct solely because it was produced: validation is performed through strict type checking, linting, production build, API contract tooling, backend/Pest test inventory, frontend Vitest, Playwright end-to-end specifications, and accessibility checks.

Human Verification and AI Verification are represented by the commands and results in section 19, the visual/UI specification in docs/ui, design-system rules in design-system/MASTER.md, focused component/permission tests, and browser-oriented Playwright specifications. The documentation pass executed frontend typecheck, lint, build and tests; its exact outcomes are stated below. No unsupported claim is made about a particular historical code-generation tool.

## 16. Customer Portal

The Customer Portal is a separate authentication and visibility surface. routes/api.php exposes portal registration, verification, login/logout, account, ticket list/create/show, messages, feedback, and guarded attachments under /api/v1/portal. PortalAuthController.php, PortalAccountController.php, PortalTicketController.php, PortalTicketMessageController.php, PortalAttachmentController.php, TicketFeedbackController.php, and GuestTicketTrackingController.php implement the API. PortalTicketScope.php and PortalAttachmentGuard.php enforce ownership/data boundaries.

Frontend evidence is frontend/src/pages/portal, frontend/src/portal/auth, PortalLayout, PortalProtectedRoute, and routes /portal/login, /portal/register, /portal/verify, /portal/tickets, /portal/tickets/new, /portal/tickets/:id, /portal/track/:token, /portal/help, and /portal/account. Customers can submit and track requests, view the owned conversation/history, download authorised files, access public FAQs/help search, and submit feedback. Tests include tests/Feature/Portal and frontend/src/__tests__/portal-isolation.test.ts.

## 17. Reports & Management

Reporting is implemented in app/Domains/Reporting. ReportController.php provides report querying, ReportExportController.php exports, ReportScheduleController.php manages schedules, GenerateReportExportJob.php executes asynchronous exports, and ReportScheduleSweepCommand.php processes due schedules. Frontend routes include /reports, /reports/:reportId, /dashboard, and /report-schedules.

Report definitions directly cover ticket volume (TicketVolumeReport.php), SLA performance (SlaPerformanceReport.php), agent performance including assigned/resolved/first-response/resolution/average-CSAT measures (AgentPerformanceReport.php), customer satisfaction (SatisfactionReport.php), backlog ageing (BacklogAgingReport.php), and management dashboard aggregation (ManagementDashboardReport.php). Reporting constraints include config/reporting.php. Historical performance evidence documents measured report/dashboard endpoint budgets in docs/api/performance-budget.md; this is supplemental to the current documentation-pass verification.

## 18. Security & Administration

Security is an explicit engineering property of the application:

| Control | Implementation evidence and behaviour |
|---|---|
| Authentication and sessions | AuthController.php, AuthenticateStaff.php, config/auth.php, config/session.php, config/sanctum.php; staff uses stateful Sanctum and login/logout regenerate session/token state. |
| 2FA and password security | TwoFactorController.php; EnableTwoFactor.php; CompleteTwoFactorChallenge.php; EnforceTwoFactorPolicy.php; StaffPasswordRules.php; confirmation/recovery workflow. |
| Users, roles and RBAC | RoleController.php, UserLifecycleController.php, PermissionKey.php, Scope.php, ResolveEffectiveScope.php, RolePolicy.php/UserPolicy.php and domain policies. |
| Route/middleware boundary | routes/api.php; public throttles and bot protection; staff auth:sanctum plus portal.deny; portal auth:portal plus portal.auth. |
| Validation | Domain Http/Requests classes, StaffPasswordRules.php, AttachmentRules.php enforce data, credentials, MIME and size policies server-side. |
| CSRF, CORS, rate limiting | bootstrap/app.php, config/cors.php, config/session.php, ProtectPublicEndpoint.php, route throttle middleware. |
| Audit logs | AuditLogger.php, AuditLog model/policy/controller, audit-log migration and security tests create append-oriented security/business trail. |
| Idempotency and concurrency | EnforceIdempotency.php, idempotency_keys migration, TicketVersionGuard and ticket concurrency tests protect retries/stale updates. |
| Webhook/API security | WebhookSigner.php, DeliverWebhookJob.php, AuthenticateApiToken.php, IssueApiToken.php; timestamp signing and plaintext-token non-persistence. |
| Data boundaries/uploads | PortalTicketScope.php, PortalAttachmentGuard.php, policies, AttachmentRules.php, private storage/filesystem configuration. |
| Secrets, retention, backup | environment variables in docker/env-additions.txt; config/security.php, retention.php, backup.php; BackupRunCommand.php, BackupVerifyCommand.php, RetentionPurgeCommand.php. |

Administration covers branches, departments, teams, users/invitations/lifecycle, role catalogue, ticket catalogue, SLA policies, automation rules, channels, security settings, audit logs, data protection, integrations, and AI operations. The route and page map is explicit in routes/api.php, frontend/src/router.tsx, frontend/src/pages/admin, and frontend/src/pages/operations.

## 19. Testing, Security Verification & Edge Cases

### Test inventory

- Backend: 130 PHP test files: 91 feature and 39 unit files; Pest lists 635 tests across security, portal, ticketing, SLA, automation, channels, customers, integrations, knowledge, reporting, observability, organisation, workspace, AI, API, and support conventions.
- Frontend: 39 Vitest files; 159 declared it/test cases. The documentation-pass run discovered 164 executable cases.
- End-to-end: seven Playwright specifications: core-workflows.spec.ts, route-smoke.spec.ts, responsive.spec.ts, tickets-v2.spec.ts, ticket-workbench-v2.spec.ts, customers-v2.spec.ts, and workspace-v2.spec.ts.

### Documentation-pass verification results (2026-09-06)

| Check | Command | Result | Evidence / scope |
|---|---|---|---|
| Frontend typecheck | cd frontend && npm run typecheck | **PASS** | TypeScript strict mode completed. |
| Frontend lint | cd frontend && npm run lint | **PASS** | ESLint --max-warnings=0 completed. |
| Frontend build | cd frontend && npm run build | **PASS** | Vite production build completed in 5.05s; 2,278 modules transformed; frontend/dist generated. |
| Frontend unit/integration | cd frontend && npm run test | Executed 39 files: **162 passed, 2 failed, 164 total** | The two assertions are in frontend/src/features/admin/api/__tests__/wire.test.ts for branch/department name mapping. |
| Backend suite inventory | docker exec ... vendor/bin/pest --list-tests | **635 tests listed** | 130 source test files: 91 feature / 39 unit. |
| Backend security alias | docker exec ... vendor/bin/pest --testsuite=Security | Completed; no tests selected | No testsuite named Security is defined; path-based invocation is applicable. |
| Targeted backend security | docker exec ... vendor/bin/pest tests/Feature/Security tests/Unit/Security | Execution began; container reported feature failures before 30-second capture window completed | Includes PHPUnit doc-comment metadata deprecation notices. |
| Backend formatting/static analysis | docker exec ... vendor/bin/pint --test && vendor/bin/phpstan analyse --no-progress | Pint reported 6 style issues; PHPStan did not run because Pint stopped the && chain | Named files are three controllers, E2eTestSeeder.php, and PortalAuthTest.php. |

### Security and edge-case coverage

Representative test/code coverage includes authorization denials and scoped access (Security policies and ResolveEffectiveScope tests), invalid ticket transitions and reopen windows (TicketTransitionMapTest.php and ReopenWindowTest.php), stale ticket versions/concurrent claim (TicketVersionGuardTest.php and TicketConcurrencyTest.php), duplicate feedback/idempotency (TicketFeedbackTest.php and EnforceIdempotency.php), attachment allowlists (AttachmentRulesTest.php), portal/staff isolation and ownership (PortalGuardIsolationTest.php and PortalTicketVisibilityTest.php), duplicate customer handling, email correlation/quoted-text parsing, provider delivery states/retries, SLA time arithmetic and working-time calculations, retention, log redaction, bad validation requests, pagination/filter API conventions, AI disabled/provider/budget conditions, and empty/loading/error UI boundaries.

Accessibility verification is present in frontend/src/design-system/__tests__/components.test.tsx: axe checks, accessible icon names, keyboard dialog focus restoration, menus/tabs, and RTL directional behaviour. LocaleProvider.tsx sets document language/direction, and responsive.spec.ts provides browser-level responsive coverage.

## 20. CI/CD & Delivery / Operational Readiness

Delivery is codified in Makefile, composer.json, frontend/package.json, docker/vhost-competition-crm.conf, docker/env-additions.txt, routes/console.php, and config files. No GitHub Actions/Azure pipeline configuration is asserted because no repository workflow file was identified; the executable local/CI command surface is Makefile and package scripts.

- **Build/deployment:** frontend build output is frontend/dist. The SPA Apache vhost uses history fallback; Laravel is served from public. Production deployment uses migrations and config cache.
- **Environment:** docker/env-additions.txt configures CORS, shared SPA/API cookie domain, Sanctum stateful domains, attachment limits/scanning boundary, public rate limits, channels, chat, reporting, AI, webhooks, ERP, imports, backups, and retention.
- **Runtime workers:** composer dev script documents queue listener; jobs support email, provider delivery, webhooks, imports and reports. Deployment must provide an appropriate queue worker.
- **Scheduler:** routes/console.php schedules backup 01:00, backup verification 02:00, retention 03:30, automatic close hourly, SLA/task/chat sweeps every five minutes, email/automation every ten minutes, and report scheduling every five minutes.
- **Operational controls:** health endpoints are in routes/api.php; backup/restore process is documented in docs/operations/backup-restore.md; backups/verification and retention have command/config evidence.

## 21. Integrations

The integration boundary is app/Domains/Integrations and app/Domains/Channels. API tokens are issued/authenticated through ApiTokenController.php, IssueApiToken.php, AuthenticateApiToken.php, and ApiTokenPolicy.php. Webhook subscriptions/deliveries use WebhookSubscriptionController.php, WebhookDeliveryController.php, DispatchWebhooks.php, DeliverWebhookJob.php, WebhookSigner.php, and configurable HTTP/Null transports. Customer ERP context uses CustomerErpContextController.php and config/integrations.php. Import runs use ImportRunController.php, RunImportJob.php, import tables and max-row/chunk settings.

Email, SMS, WhatsApp, webhooks, ERP, AI providers, and external delivery endpoints are configuration-driven boundaries. docker/env-additions.txt exposes the non-secret parameter names, while deployment supplies actual credentials/secrets.

## 22. Platform Capabilities

- **Arabic and English:** frontend/src/i18n/ar.json and en.json; backend lang/ar and lang/en error catalogues.
- **RTL and LTR:** LocaleProvider.tsx sets documentElement.dir to rtl for Arabic and ltr for English; design-system components include RTL tests.
- **Responsive/mobile friendly:** responsive.spec.ts, responsive component styles, mobile staff navigation and accessible shared primitives.
- **Multi-department / multi-branch:** BranchController.php, DepartmentController.php, TeamController.php, UserPlacementController.php; branches/departments/teams and working-calendar migrations; scoped permissions.
- **Custom branding/design:** design-system/MASTER.md defines blue/orange palette, Plus Jakarta Sans, tokens, accessible components, focus and reduced-motion rules; frontend/src/design-system implements shared UI patterns.

## 23. Maintainability & Code Quality

Maintainability is evidenced by domain boundaries, actions/services/policies/Form Requests, reusable React design-system components, strict TypeScript, generated API client workflow, OpenAPI contract freeze, migrations, configuration modules, queues/jobs, scheduler commands, translations, shared API error handling, and test organisation.

Concrete examples: Ticketing separates TicketLifecycleController.php from actions/services and transition maps; Knowledge separates search/visibility/lifecycle services; AI separates provider/gate/privacy/budget services; Integrations separates signer/transports/jobs; frontend separates app shell, auth, portal, features, design-system and generated API code. Generated client output is not hand-edited: frontend/package.json uses npm run api:generate and README workflow references docs/api/openapi.yaml.

## 24. Technical Engineering Decisions

- **Asynchronous jobs:** inbound email, provider delivery, webhooks, imports, and report exports use jobs so network/large-data work does not block HTTP requests. Evidence: ProcessInboundEmailJob.php, SendProviderMessageJob.php, DeliverWebhookJob.php, RunImportJob.php, GenerateReportExportJob.php.
- **Scheduled SLA processing:** SLA clock/breach and automation work are repeatedly swept rather than recalculated only in user requests. Evidence: SlaClockService.php, AutomationSweepCommand.php, routes/console.php.
- **Policies and middleware:** authorization is server-side in policies/scopes and route middleware, while frontend guards improve UX but do not replace enforcement. Evidence: domain Policies, ResolveEffectiveScope.php, routes/api.php.
- **Append-oriented history:** ticket/customer events and audit logs preserve interaction and security history separately from current records. Evidence: TicketEvent.php, CustomerEvent.php, AuditLogger.php and migrations.
- **Provider abstraction:** Null/HTTP transports, provider interfaces, config gates, and signed webhooks decouple the CRM from a single external vendor. Evidence: AiProvider.php, NullAiProvider.php, WebhookTransport.php, HttpWebhookTransport.php, config files.
- **Idempotency/concurrency:** idempotency keys and ticket version guards reduce duplicate writes and stale updates in distributed/browser retry scenarios. Evidence: EnforceIdempotency.php; idempotency migration; TicketVersionGuardTest.php.
- **Typed SPA/API boundary:** OpenAPI plus generated client, React query hooks, envelope/error normalisation, and TypeScript strict mode reduce contract drift. Evidence: docs/api/openapi.yaml, frontend/orval.config.ts, frontend/src/api/http.

## 25. Additional Engineering Capabilities

Beyond the baseline CRM scope, repository evidence includes ticket merge/split, watchers, links, tags, saved views, delivery event/retry handling, working-hour SLA clocks and pauses, scheduled reports, configurable webhooks and delivery records, API tokens, imports, idempotency, audit/retention/backup operations, 2FA, OpenAPI contract freeze, generated client workflow, public guest tracking, public forms, live chat, accessibility testing, request IDs, health endpoints, and configurable AI privacy/budget controls.

## 26. Evaluation Criteria Crosswalk

| Evaluation criterion | Strongest implementation evidence | Verification evidence |
|---|---|---|
| Requirement & Specification | Sections 2–3; docs/ui modules; routes/api.php; frontend/src/router.tsx | Matrix includes all CRM scope groups and exact paths |
| Planning & Task Breakdown | Section 4; FRONTEND_REBUILD_EXECUTION.md; docs/contracts; Git milestones | Workstream objectives, dependencies, artifacts and strategies are explicit |
| AI Usage & Verification | Section 15; strict TypeScript/lint/build/test commands; accessibility and E2E inventory | Current typecheck/lint/build PASS; test results recorded in section 19 |
| Engineering Foundations | Section 5; domain layout, routing, OpenAPI, auth, policies, queues, config | API/frontend component and contract test inventory |
| Database / Data Model | Section 6; 104 migrations, UUID trait, models/tables | Feature/unit tests exercise customer, ticket, SLA, portal and security state |
| End-to-End Flows | Section 7; UI → route → controller → policy → action/service → database → UI | Ticket/customer/portal/workspace E2E and focused test paths |
| Delivery / Operational Readiness | Section 20; Makefile, Docker vhost, env config, scheduler, backups | Production build PASS; commands are reproducible |
| Maintainability | Section 23; domains, actions/services, typed contract, translations/tests | strict TypeScript and ESLint PASS in documentation run |
| Testing, Security & Edge Cases | Sections 18–19; security model, 635 listed backend tests, frontend/E2E inventory | Exact executed command results, a11y/RTL and edge-case paths |
| Technical Understanding | Section 24; queues, scheduler, policies, audit history, integration abstractions, idempotency | Source paths and test/code evidence for each decision |

## 27. Local Setup & Running the Application

Prerequisites are Docker/Docker Compose, Node.js 18+, and Make. The canonical local hosts are competition-crm.azmsquad.localhost for the Laravel API and app.competition-crm.azmsquad.localhost for the React SPA. Add both hostnames to the local hosts file as documented by the vhost setup.

    docker exec -w /var/www/html/competition-crm azm-php82 composer install
    make artisan cmd="key:generate"
    make artisan cmd="migrate"
    make artisan cmd="db:seed"

    cd frontend
    npm ci
    npm run api:generate
    npm run dev

The Docker PHP runtime expected by Makefile is azm-php82. Configure the SPA/API cookie domain, Sanctum stateful hosts, and CORS origins using docker/env-additions.txt. The SPA vhost serves frontend/dist and Laravel vhost serves public.

For runtime operations, start a queue worker using the Composer dev workflow or an equivalent deployment worker, and run Laravel scheduling through the configured scheduler:

    php artisan queue:listen --tries=1 --timeout=0
    php artisan schedule:work

## 28. Reproducing Verification

    # Backend quality and tests
    make test
    make lint
    make api-lint

    # Frontend quality, test, build, and E2E
    make fe-test
    make fe-build
    cd frontend && npm run test:e2e

    # API client after contract change
    cd frontend && npm run api:generate

The exact documentation-pass outcomes are in section 19. E2E runtime verification requires the canonical hosts, browser setup, and active SPA/API runtime described by frontend/playwright.config.ts and docker configuration. Provider delivery verification requires the corresponding deployment credentials.

## 29. Supporting Documentation

This README is the complete evaluator-facing summary. The following files provide deeper/raw evidence, historical detail, and procedure references:

- docs/EVALUATION_EVIDENCE.md
- docs/REQUIREMENTS_TRACEABILITY.md
- docs/VERIFICATION_REPORT.md
- docs/ui/00-overview.md through docs/ui/13-public-surfaces.md
- docs/api/openapi.yaml and docs/contracts/api-contract-freeze.md
- docs/operations/backup-restore.md
- docs/api/performance-budget.md
