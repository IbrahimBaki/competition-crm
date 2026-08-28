# 09. AI Assistance

**Domains**: Ai  
**Surface**: Staff CRM only  
**Permissions**: `ai.*`

---

## Critical AI UX Rule

**AI suggestions are NEVER auto-applied.** Every AI output must render as a suggestion card with explicit **Accept / Discard** actions. Low-confidence classifications must visibly flag "needs review" rather than auto-assigning.

---

## AI Suggestion Card Component (Reused Everywhere)

**Used in**: Ticket reply composer (suggested reply), ticket classification (category suggestion), ticket detail (summary), knowledge base (suggested articles), chatbot handoff (confidence indicator).

**Component Props**:
```
type: 'summary' | 'suggested_reply' | 'classification' | 'suggested_article' | 'chatbot_response'
content: string (the AI-generated text)
confidence: number (0–1, optional)
onAccept: callback
onDiscard: callback
onLearnMore: callback (optional)
```

**Rendering** (visual spec):
```
┌─────────────────────────────────────────┐
│ ✨ AI Suggestion: Suggested Reply      │
├─────────────────────────────────────────┤
│ Confidence: ████████░░ 83%              │
│                                         │
│ "Thank you for contacting us. We've    │
│  received your request and will...     │
│                                         │
│ [Accept]  [Discard]  [Learn More]      │
└─────────────────────────────────────────┘
```

**Colors & Badges**:
- Confidence >= 75%: green (high confidence)
- Confidence 50–74%: yellow (medium, flag-icon)
- Confidence < 50%: red + "⚠️ Low Confidence — Review Required"

**User Actions**:
| Label | Behavior | Permission | Notes |
|---|---|---|---|
| Accept | Use suggestion + record acceptance | `ai.suggestions.resolve` | On accept: `POST /ai/suggestions/{suggestion}/resolve/{decision}` (`decision` = `accepted`) |
| Discard | Reject suggestion + record rejection | (same) | On discard: POST with state=discarded; show alternative suggestions (if any) |
| Learn More | Navigate to detailed explanation (if applicable) | (same) | For classifications: show "Why this category?" with confidence breakdown |

**Related endpoints**:
- (Suggestion saved when AI generates it, returned in response; user resolves via suggestion resolution endpoint)

---

## Ticket Reply Composer with AI

**Route**: Inline on ticket detail, in compose area  
**Purpose**: Compose reply, optionally get AI-suggested reply  

**Composition Area**:
```
┌─────────────────────────────────────────────┐
│ [Text Editor]                              │
│ "Thank you for reaching out..."            │
│                                             │
│ [✨ Get AI Suggestion]  [Send]  [Cancel]    │
└─────────────────────────────────────────────┘
```

**AI Suggestion Modal** (on click [✨ Get AI Suggestion]):
- Shows suggestion card (see component above)
- [Accept Suggestion] → fills compose area with AI text (user can edit before sending)
- [Discard] → hides suggestion, user continues with manual text
- [Try Different Tone] → button to regenerate with different tone (formal, casual, empathetic, etc.) — optional

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Get AI Suggestion | POST generate suggestion | (internal, likely returns suggestion in response shape) | `ai.assistance.use` | Yes (same ticket + prompt = same suggestion, cached) |
| Accept Suggestion | (fills compose box, no API call yet) | — | (same) | N/A |
| Send Reply | POST message + resolve suggestion as accepted | `POST /tickets/{ticket}/messages` + `POST /ai/suggestions/{suggestion}/resolve/{decision}` | (same) | No (message needs Idempotency-Key) |

**Related endpoints**:
- (AI suggestion generated server-side, returned with ticket context or via separate POST)
- `POST /tickets/{ticket}/messages` (send reply)

---

## Ticket Classification with AI

**Route**: Ticket detail, category selector  
**Purpose**: Auto-suggest ticket category/priority based on content  

**Category Selector** (normal dropdown):
```
[Technical Support ▼] [Suggested: Technical / Troubleshooting (92% confidence)]
```

**Suggested Classification Badge**:
- Shows when AI classification available
- Confidence % + [Accept] [Dismiss] buttons inline
- If confidence < 75%: flag "⚠️ Review before assigning"

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Accept Suggestion | Auto-select suggested category + resolve suggestion | (PATCH ticket with category + resolve suggestion) | `ai.assistance.use` | Yes |
| Dismiss | Keep current category + discard suggestion | (resolve suggestion as discarded) | (same) | Yes |
| Manually Select | User picks from dropdown (no AI involved) | (PATCH ticket) | (same) | N/A |

**Related endpoints**:
- (AI classification runs on ticket creation/update, returned in ticket response with AiSuggestion object)

**Notes**:
- If confidence < 75%: suggestion shown but NOT auto-applied; ticket gets `ai_classification_needs_review` event
- If confidence >= 75%: suggestion shown, but still requires explicit accept
- Classification affects SLA target (different categories have different SLA policies)

---

## Suggested Articles (in Ticket Detail)

**Route**: Ticket detail sidebar, "Knowledge" section  
**Purpose**: AI-surfaced KB articles relevant to ticket  

**Widget**:
```
💡 Suggested Articles
┌─────────────────────────────┐
│ • How to Reset Password     │
│   "Applies to this category"│
│                              │
│ • Troubleshooting Tips      │
│   "Related to keywords in   │
│    ticket subject"           │
│                              │
│ [View All Articles] [Dismiss]│
└─────────────────────────────┘
```

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Click Article | Navigate to article in KB (new tab) | `/knowledge/articles/{id}` | (same) | N/A |
| View All | Show full suggestion list (modal) | (no API call) | (same) | N/A |
| Dismiss Suggestions | Hide widget for this ticket | (record discard action, hide UI) | (same) | N/A |

---

## AI Settings (Admin)

**Route**: `/admin/settings/ai` or `/admin/ai-settings`  
**Purpose**: Configure AI features globally  
**Permission**: `ai.settings.manage`  

**Toggles** (per feature):

| Feature | Enabled | Confidence Threshold | Notes |
|---|---|---|---|
| Suggested Replies | ✓ | N/A | Generate reply suggestions in composer |
| Classification | ✓ | 0.75 (75%) | Auto-suggest categories; flag if below 75% |
| Ticket Summary | ✓ | N/A | AI-generated ticket summary |
| Suggested Articles | ✓ | N/A | Link related KB articles |
| Chatbot | ✓ | N/A | Live chat bot (see below) |

**Global Settings**:
| Setting | Value | Notes |
|---|---|---|
| AI Provider | OpenAI / Custom API | Which service to call |
| Budget Monthly | 10,000 units | Cost limit per month (API calls) |
| Block When Over Budget | ✓ | Stop AI features when budget hit |
| Privacy Redaction | ✓ | Redact customer PII before sending to AI |
| Timeout (seconds) | 15s | Max wait for AI response |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Toggle Feature | PATCH setting | (endpoint TBD, likely PATCH /ai/settings or similar) | (same) | No |
| Save Settings | PATCH settings | (same) | (same) | No |

**Related endpoints**:
- (Settings endpoint TBD in final OpenAPI)

**Notes**:
- Disabling a feature: hides all AI suggestion UIs
- Budget system: tracks API call costs, blocks when limit hit (shows "AI features temporarily unavailable" message)
- Privacy: PII redaction masks customer emails/phones before sending to AI, de-identifies internal names

---

## AI Usage Log (Admin)

**Route**: `/admin/ai/usage` or `/admin/settings/ai-usage`  
**Purpose**: Monitor AI feature usage and costs  
**Permission**: `ai.usage.view`  

**Summary Cards**:
```
📊 AI Usage This Month
├─ API Calls: 1,250 / 2,000 budget (62%)
├─ Cost: $45.60
├─ Suggested Replies: 340 used, 28 accepted (8.2%)
├─ Classifications: 220 suggested, 180 accepted (82%)
└─ Articles: 150 suggestions shown
```

**Detailed Table** (expandable):

| Date | Feature | Tickets | Usage | Outcome | Cost |
|---|---|---|---|---|---|
| 2026-08-27 | suggested_reply | TKT-042 | 1 call | accepted | $0.03 |
| 2026-08-27 | classification | TKT-041 | 1 call | accepted | $0.02 |
| 2026-08-26 | suggested_articles | TKT-040 | 1 call | dismissed | $0.01 |

**Filters**:
- Date range
- Feature (all, suggested_reply, classification, articles, summary, chatbot)
- Outcome (accepted, discarded, ignored, error)

**Related endpoints**:
- (Usage/analytics endpoint TBD)

**Notes**:
- Useful for tracking ROI (e.g., if only 8% of suggested replies are accepted, may need to improve model or adjust threshold)
- Budget warnings: banner alert at 75%, 90%, 100% of budget consumed

---

## Chatbot Handoff (AI → Human)

**Route**: Live chat, after failed AI responses  
**Purpose**: Escalate from AI chatbot to human agent when confidence is low or max retries reached  

**Chatbot Behavior**:
- Customer asks question in live chat widget
- AI bot attempts to answer (using knowledge base + customer context)
- If confidence < threshold OR user says "I'd like to talk to someone": escalate
- Max 2 failed attempts (customer says "that didn't help") before mandatory escalation

**Escalation UI** (in chat widget):
```
Chatbot: "It sounds like you need a password reset. 
         [Help with Password Reset]  [Talk to Agent]"

(User clicks [Talk to Agent])

System: "Connecting you to the next available agent..."
Queue message: "Position: 2, estimated wait: 3 minutes"

(Agent accepts)

Agent: "Hi, I'm Ahmed from Support. How can I help?"
```

**Actions** (bot, server-side):
| Label | Condition | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Auto-Answer | Customer message + high confidence (≥75%) | (chatbot model call) | `ai.assistance.use` | No |
| Offer Alternatives | Confidence medium (50–74%) | (return 2–3 article links + human option) | (same) | N/A |
| Escalate | Low confidence (<50%) OR 2 failed attempts | `POST /channels/chat/sessions/{session}/transfer` ⚠️ **NOT IMPLEMENTED** (to queue) | (same) | Yes |

**Configuration** (in AI Settings):
- Max failed attempts: 2 (before mandatory escalation)
- Confidence threshold for auto-answer: 0.75
- Confidence threshold for article-only: 0.50

---

## Navigation Map

- **Ticket Detail**: Compose area [✨ Get AI Suggestion] → **Suggestion Card**
- **Ticket Detail**: Category selector → **Suggested Classification Badge**
- **Ticket Detail**: Sidebar "Knowledge" section → **Suggested Articles Widget**
- **Sidebar: Admin > Settings**
  - **AI Settings** (toggles, provider, budget)
  - **AI Usage Log** (analytics)
- **Live Chat Widget**: chatbot flow → **Escalation to Agent** (if needed)

---

**Next**: [10-reporting.md](10-reporting.md)
