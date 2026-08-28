# AI Assistance & Chatbot — Data Policy & Safety

## Scope

This document governs AI-assisted features across ticketing (summary, suggested reply, auto-classification, suggested articles) and customer-facing chatbot. All features operate under strict server-side enforcement of human approval before any AI-generated content reaches customers.

## Personal Data Handling

### Data Sent to Provider

The following fields are **sent** to the AI provider, after redaction:

- Ticket body and message thread (customer messages, internal agent notes redacted)
- Ticket metadata: customer name, ticket subject, category, priority
- Message channel and direction

### Data **Never** Sent

- Attachments (file content and filenames always dropped)
- Customer email addresses (redacted to `[email]`)
- Phone numbers (redacted to `[phone]`)
- National/ID numbers (redacted to `[id]`)
- API tokens and credentials (redacted to `[redacted]`)
- Internal-only notes and comments

### Redaction Rules

Redaction applies **before** any payload leaves the system (see `app/Domains/Ai/Services/Privacy/AiPayloadSanitiser.php`). Rules:

- Email: `user@domain.com` → `[email]`
- Phone: `+1-555-0123` → `[phone]`
- ID numbers: `123456789` → `[id]`
- Tokens/keys: `api_key=secret123` → `api_key=[redacted]`
- Arabic text: preserved in full (only sensitive patterns redacted)

## Retention & Purge

| Table | Retention | Purge Behavior |
|-------|-----------|----------------|
| `ai_suggestions` | 90 days | Delete entire row by created_at |
| `ai_usage_records` | 180 days (cost), 30 days (content) | Strip prompt content immediately; retain token/cost columns for 6 months |

## Provider Failure & Fallback

When the AI provider is unavailable:

1. **Feature disabled** → HTTP 409 + distinct error code
2. **Provider unreachable** → HTTP 503 + distinct dependency code
3. **Budget exhausted** → HTTP 429 + distinct code

**Fallback behavior:**

- Agent-facing features (summary, suggested reply) → error returned to UI
- Auto-classification → ticket routed to human review queue
- Suggested articles → unranked knowledge search results returned (degraded)
- Chatbot → handoff to human agent (no silent failure)

No ticket, chat, SLA, or automation workflow is **ever blocked** by AI failure.

## Compliance

- ✓ No hardcoded user-facing strings (all localized in `lang/en/errors.php`, `lang/ar/errors.php`)
- ✓ Bilingual support: all customer-visible AI text (chatbot label, handoff message) in ar/en
- ✓ Audit trail: every AI suggestion accept/discard recorded via `AuditLogger`
- ✓ Zero cost for `NullAiProvider` (local testing and production CI/CD unaffected)

## Configuration

Master kill-switch: `AI_ENABLED=false` (default) disables **all** AI features instantly with zero provider calls.

Per-feature toggles allow independent control:
- `AI_FEATURE_SUMMARY`
- `AI_FEATURE_SUGGESTED_REPLY`
- `AI_FEATURE_CLASSIFICATION`
- `AI_FEATURE_SUGGESTED_ARTICLES`
- `AI_FEATURE_CHATBOT`

Budget enforcement: `AI_BUDGET_LIMIT_MICROS` (0 = unlimited for testing; non-zero blocks when spent exceeds).
