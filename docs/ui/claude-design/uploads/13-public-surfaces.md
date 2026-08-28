# 13. Public Surfaces

> **Backend availability — read before building.**
> The chat widget can only **open** a session (`POST /channels/chat/sessions`).
> Sending messages, fetching the transcript and ending a session have no routes,
> so a working widget cannot be built yet.

**Domains**: Channels (WebForm, Chat), Knowledge (public), Portal (guest)  
**Surface**: Public/Anonymous (no auth required)  
**Access**: Token-based (for guest tracking) or no authentication  

---

## Public Web Form (Embeddable)

**Route**: `/channels/public/web-forms/{formKey}` (public, no auth)  
**Purpose**: Customer intake form, embeddable on website  

**Rendering**:
- Standalone page OR embeddable iframe: `<iframe src="https://crm.example.com/channels/public/web-forms/contact-us"></iframe>`
- Self-contained form (no CRM chrome, minimal branding)

**Form Display**:
- Form title (bilingual: ar/en, depending on browser Accept-Language)
- Form description (optional)
- Dynamically rendered fields per form config (text, email, textarea, select, date, etc.)
- Submit button

**Example Form** (Contact Us):
```
Contact Us
─────────────────────────

Name *: [________]
Email *: [________]
Phone: [________]
Category: [Technical ▼]
Message *: [________________
            ________________]

[Submit]
```

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Submit Form | POST submission | `POST /channels/public/web-forms/{formKey}/submissions` | (none) | No (needs Idempotency-Key) |

**Validation**:
- Client-side: required fields, email format, phone format (if applicable)
- Server-side: same validation + spam checks (rate limit, CAPTCHA if configured)

**States**:
- **Normal**: form ready
- **Loading**: button disabled, spinner
- **Validation error**: field-level errors highlighted
- **Success**: "Thank you! Your message has been received. Ticket reference: TKT-042" (optional ticket creation notification)
- **Error**: generic error message + [Retry] button

**Related endpoints**:
- `GET /channels/public/web-forms/{formKey}` (fetch form metadata + field definitions)
- `POST /channels/public/web-forms/{formKey}/submissions` (submit)

**Notes**:
- Form config includes department routing (auto-assign submission to department)
- Spam protection: rate limiting + optional CAPTCHA (after N submissions from same IP)
- Submission tracking token issued in success response (for guest tracking link in email)

---

## Submission Status Tracker (Public, No Auth)

**Route**: `/channels/public/web-forms/submissions/{trackingToken}` (public, no auth)  
**Purpose**: Check status of submitted web form (before account creation)  

**Display** (minimal):
```
Submission Status
─────────────────

Thank you for submitting the form!

Reference: TKT-042
Status: Open
Last Updated: 2 hours ago

Message: "Our team received your inquiry and is reviewing it. You can also create an account to track your ticket in real-time."

[Create Account] [Contact Support]
```

**Actions** (if applicable):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Create Account | Link to `/portal/register` (pre-fill email if available) | (navigation) | (none) | N/A |

**Related endpoints**:
- `GET /channels/public/web-forms/submissions/{trackingToken}` (fetch submission status, no auth required)

**Notes**:
- Encourages form submitter to create account for full tracking + ticket reply capability
- Tracking token valid for ticket lifetime + 30 days after closure

---

## Public Help Center

**Route**: `/help` or `/public/knowledge` (public, no auth)  
**Purpose**: Customer-facing knowledge base search + browse  
**Visibility**: Articles with visibility = `public` only (or `customers` if user logged in as customer)  

### Search Interface
```
Search Help Center
[_______________________] [Search]

Browse by Category
├─ Technical Support (5)
├─ Billing & Accounts (3)
└─ Getting Started (2)
```

**Features**:
- **Global Search** (full-text, bilingual ar/en)
  - Debounced autocomplete (shows top 5 matching article titles)
  - Enter/click search → search results page
- **Browse by Category** (hierarchical tree)
  - Click category → list of articles in category
  - Breadcrumb: Help > Category > Article

### Search Results
```
Search: "password reset"

Results (1-10 of 23)

1. How to Reset Your Password
   Category: Technical Support
   Updated: 2 days ago
   Helpful: 👍(125) 👎(3)

2. Troubleshooting Login Issues
   Category: Getting Started
   Updated: 1 week ago
   Helpful: 👍(43) 👎(2)

[Load More Results]
```

**Columns**:
- Article title (clickable)
- Category
- Last updated (relative time)
- Helpful count (thumbs up/down aggregate)

### Article Detail View
```
Technical Support > How to Reset Your Password
═══════════════════════════════════════════════

1. Click "Forgot Password" on the login page
2. Enter your email address
3. Check your email for a reset link (valid for 24 hours)
4. Click the link and create a new password
5. Log in with your new password

Was this helpful?
[👍 Helpful (125)] [👎 Not Helpful (3)]
```

**Features**:
- Full article content (bilingual, respects Accept-Language header + manual toggle)
- Related articles (suggested AI or editorial links)
- Helpful feedback buttons (no login required, track IP-based)
- Share buttons (email, copy link, social — optional)

**Language Toggle** (if article available in both ar/en):
- Flag icon or text link: "عربي | English"
- Click to switch between versions

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Mark Helpful | POST feedback | `POST /public/knowledge/articles/{article}/feedback` (helpful=true) | (none) | Yes |
| Mark Not Helpful | POST feedback | `POST /public/knowledge/articles/{article}/feedback` (helpful=false) | (none) | Yes |

**Related endpoints**:
- `GET /public/knowledge/categories` (category tree)
- `GET /public/knowledge/articles` (list with pagination)
- `GET /public/knowledge/articles/search?q=...` (full-text search)
- `GET /public/knowledge/articles/{article}` (article detail)
- `POST /public/knowledge/articles/{article}/feedback` (thumbs up/down, no auth)

**Notes**:
- Search is bilingual (query matches both ar/en content)
- Feedback counts shown to all visitors (social proof)
- Related articles driven by tags or semantic similarity
- No login required for search or feedback

---

## Live Chat Widget (Embeddable)

**Route**: `/channels/chat/sessions` (public, initiate session endpoint)  
**Purpose**: Customer initiates chat with support team (embeddable on website)  

### Chat Widget (Bubble + Window)

**Bubble** (default state):
```
┌──────┐
│  💬  │  ← Floating bubble, bottom-right corner
└──────┘
```

**On Click** (open chat window):
```
┌────────────────────────┐
│ Support Chat       [X] │  ← Title + close button
├────────────────────────┤
│ Hi, how can we help?   │  ← Bot greeting or queue message
│ [Agent typing...]      │
│                        │
│ [Message input]        │
│ [Send]                 │
└────────────────────────┘
```

### Chat Session Flow

**Step 1: Visitor Info** (optional, can skip)
```
How can we help?

Name: [____]
Email: [____]

[Start Chat]  [Skip]
```

**Step 2: Queue/Bot Response**
```
Thank you for contacting us!

Position in queue: 2
Estimated wait: 3 minutes

(Or: AI bot answers, with [Talk to Agent] option if needed)
```

**Step 3: Live Chat** (once agent accepts)
```
Agent: "Hi, I'm Ahmed. How can I help?"
Visitor: "I can't reset my password"
Agent: "Let me help with that..."
```

**Features**:
- **Typing indicators**: "Agent is typing..."
- **Session persistence**: if visitor closes tab and returns within 5 min (reconnect window), resume same session
- **Transcript history**: session saved, accessible via guest tracking token (optional)
- **File upload**: (optional, if configured) visitor can upload screenshots/files

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Start Session | POST session | `POST /channels/chat/sessions` (no auth) | (none) | No |
| Send Message | POST message | `POST /channels/chat/sessions/{session}/messages` ⚠️ **NOT IMPLEMENTED** | (none) | No (needs Idempotency-Key) |
| End Session | POST end | `POST /channels/chat/sessions/{session}/end` ⚠️ **NOT IMPLEMENTED** | (none) | Yes |
| Transfer | (triggered by agent) | (internal) | (none) | N/A |

**States**:
- **Offline**: "We're currently offline. [Contact Form] or email us."
- **Queued**: queue position + estimated wait time
- **Active**: live conversation
- **Bot Handoff**: AI bot unable to help → [Talk to Agent] triggers escalation
- **Closed**: "Session ended. Thank you for chatting with us."

**Related endpoints**:
- `POST /channels/chat/sessions` (initiate session, returns session_id)
- `GET /channels/chat/sessions/{session}/messages` ⚠️ **NOT IMPLEMENTED** (get transcript)
- `POST /channels/chat/sessions/{session}/messages` ⚠️ **NOT IMPLEMENTED** (send message)
- `POST /channels/chat/sessions/{session}/end` ⚠️ **NOT IMPLEMENTED** (close session)

**Notes**:
- Queue management: max 20 sessions queued, max 3 concurrent per agent
- Reconnect window: 5 minutes (allow tab close/switch without losing session)
- Idle timeout: 15 minutes (auto-close session if no messages)
- Availability: configurable per branch working hours (or 24/7 if branch.is_24_7 flag set)
- Bot integration: if available, bot attempts to answer before queuing to human agent
- Transcript retention: 90 days (per config)

---

## Styling & Branding

**Widget Customization** (admin-configurable per channel):
- Primary color (brand color for buttons, links)
- Logo (displayed in chat header)
- Title (e.g., "Support Chat", "Help Center")
- Greeting message
- Font (optional, system default or custom)

**Related endpoints**:
- (Widget config likely stored in channel settings, fetched on page load)

---

## Navigation Map

- **Embedded Web Form** → `/channels/public/web-forms/{formKey}` → submit → success page with tracking token
- **Tracking Link** (email) → `/channels/public/web-forms/submissions/{token}` → view status → [Create Account]
- **Help Center** → `/help` → search or browse → article detail → feedback → related articles
- **Live Chat** → bubble on website → open widget → queue → chat with agent (or bot first)
- **Public Gateway**: all public routes accessible WITHOUT authentication, NO login required

---

**End of Module Files**

All 13 module-specific documentation files complete. Now proceed to Claude Design with the full specification set: `docs/ui/00-overview.md` + `docs/ui/01-..md` through `docs/ui/13-public-surfaces.md`. Each module file is self-contained and cross-referenced via "Next" pointers at the end.
