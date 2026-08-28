# 07. Knowledge

> **Backend availability — read before building.**
> Knowledge articles and categories **cannot be deleted** — articles move
> through states instead (`POST /knowledge/articles/{article}/state`, including
> `archived`). Article feedback is a public-surface endpoint only:
> `POST /public/knowledge/articles/{article}/feedback`.

**Domains**: Knowledge  
**Surface**: Staff (authoring/management) + Public (help center view)  
**Permissions**: `knowledge.*`

---

## KB Category Manager

**Route**: `/admin/knowledge/categories`  
**Purpose**: Manage knowledge base category hierarchy  
**Permission**: `knowledge.categories.manage`  

**Category Tree** (collapsible, drag-to-reorder):
```
Technical Support
├─ Installation
├─ Troubleshooting
└─ Best Practices
Billing & Accounts
├─ Payment Methods
└─ Subscription
[+ Add Category]
```

**Actions** (per category):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Modal to edit category | `/admin/knowledge/categories/{id}` | (same) | N/A |
| Delete | DELETE (if no articles in category) | `DELETE /knowledge-categories/{category}` ⚠️ **NOT IMPLEMENTED** | (same) | Yes |
| Add Sub-Category | Link | — | (same) | N/A |

**Category Edit Modal**:
**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Name | string | Yes | Yes | Category label (ar/en) |
| Parent | select | No | No | For nesting |

**Related endpoints**:
- `GET /knowledge/categories` (hierarchy)
- `POST /knowledge/categories`, `PATCH /knowledge/categories/{id}`, `DELETE /knowledge/categories/{category}` ⚠️ **NOT IMPLEMENTED**

---

## KB Article List Screen

**Route**: `/knowledge` or `/admin/knowledge/articles`  
**Purpose**: Browse and manage all KB articles  
**Permission**: `knowledge.articles.view`  

**Table**:
| Title | State | Visibility | Author | Created | Last Updated | Actions |
|---|---|---|---|---|---|---|
| How to Reset Password | published | public | Ahmed | 2026-08-01 | 2026-08-20 | Edit / Archive / Delete |
| API Integration Guide | in_review | internal | Sarah | 2026-08-15 | 2026-08-27 | Edit / Publish |
| FAQ (often asked) | draft | internal | Admin | 2026-08-25 | 2026-08-25 | Edit / Archive |

**Filters**:
- State (draft, in_review, published, archived)
- Visibility (public, customers, internal)
- Author (user select)
- Category (select)
- Created/Updated date range

**Bulk Actions**:
- [ ] Select multiple
- **Publish Selected** (draft → published)
- **Archive Selected**
- **Change Visibility** (dropdown)
- **Export** (CSV/PDF)

**Actions** (per article):
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Edit | Navigate to editor | `/knowledge/articles/{id}/edit` | `knowledge.articles.update` | N/A |
| Publish | Change state to published | `POST /knowledge/articles/{article}/state` (state=published) | `knowledge.articles.publish` | Yes |
| Archive | Change state to archived | `POST /knowledge/articles/{article}/state` (state=archived) | `knowledge.articles.archive` | Yes |
| Delete | DELETE | `DELETE /knowledge/articles/{article}` ⚠️ **NOT IMPLEMENTED** | (same) | Yes |

**Related endpoints**:
- `GET /knowledge/articles` (list with filter/sort)
- `POST /knowledge/articles/{article}/state` (publish/archive workflow)

---

## KB Article Editor

**Route**: `/knowledge/articles/new` (create) or `/knowledge/articles/{id}/edit` (edit)  
**Purpose**: Author/edit knowledge base article  
**Permission**: `knowledge.articles.create` (new) or `knowledge.articles.update` (edit)  

**Section 1: Metadata**

**Fields**:
| Field | Type | Required | Bilingual | Notes |
|---|---|---|---|---|
| Title | string | Yes | Yes | Article heading (ar/en) |
| Slug | string | Yes (auto-generated) | No | URL-safe identifier (auto-slug from title) |
| Category | select | Yes | No | Parent category |
| Visibility | enum | Yes | No | public (customers + staff), customers (customers only), internal (staff only) |

---

**Section 2: Content**

**Rich Text Editor** (dual inputs for ar/en):
- **Arabic editor** (right-aligned, RTL)
- **English editor** (left-aligned, LTR)
- Toolbar: Bold, Italic, Lists, Links, Images, Code block, Blockquote, Heading levels
- Paste images: auto-upload + embed
- Auto-save drafts every 30s

---

**Section 3: Actions & Workflow**

**Current State**: Draft / In Review / Published / Archived  
**Action Buttons** (permission-gated):

| Label | Action | Endpoint | Permission | Idempotent | Condition |
|---|---|---|---|---|---|
| Submit for Review | POST state | `POST /knowledge/articles/{article}/state` (state=in_review) | (create/update) | Yes | State is draft |
| Publish | POST state | `POST /knowledge/articles/{article}/state` (state=published) | `knowledge.articles.publish` | Yes | State is in_review or draft |
| Archive | POST state | `POST /knowledge/articles/{article}/state` (state=archived) | `knowledge.articles.archive` | Yes | State is published |
| Restore from Archive | POST state | `POST /knowledge/articles/{article}/state` (state=draft) | (same) | Yes | State is archived |
| Save Draft | Implicit (autosave) or explicit [Save] button | `PATCH /knowledge/articles/{article}` | (same) | No | — |
| Cancel | Go back | — | N/A | N/A | — |

**Version History** (accordion, collapsible):
- Table: Version #, Created by, Date, Status, [View] [Restore]
- View modal: shows article content at that version (read-only)
- Restore button: revert to this version (creates new version entry)

**Related endpoints**:
- `POST /knowledge/articles` (create)
- `PATCH /knowledge/articles/{article}` (update)
- `POST /knowledge/articles/{article}/state` (publish/archive)
- `GET /knowledge/articles/{article}/versions` (version history)
- `POST /knowledge/articles/{article}/versions/{version}/restore` (restore)

**Notes**:
- State machine: draft → in_review → published ↔ archived
- Autosave every 30s to avoid losing work
- Version control: each save increments version number
- Publish workflow: editors submit, reviewers/admins publish (optional 2-stage approval)

---

## Public Help Center (Search + Browse)

**Route**: `/help` or `/public/knowledge` (public, no auth required)  
**Surface**: Public  
**Visibility**: Only articles with visibility = public or customers (if logged in as customer)  

**Layout**:

### Left Sidebar: Category Browse
```
Browse by Category
├─ Technical Support (5 articles)
├─ Billing & Accounts (3 articles)
└─ Getting Started (2 articles)
```

### Center: Search & Results
**Search Bar**:
- Full-text search across titles + body (for both ar/en)
- Autocomplete suggestions (top 10 matching articles)
- Search button or Enter to submit

**Search Results**:
```
1. How to Reset Password
   Helpful? 👍(12) 👎(2) | Category: Technical Support

2. Account Locked – Why and How to Fix
   Helpful? 👍(8) 👎(1) | Category: Troubleshooting
   
3. [Load More Results]
```

### Article Detail View (clicked from search/category)
- Full article content (ar/en language picker at top)
- Breadcrumb: Help > Category > Article Title
- Feedback section: "Was this helpful?" with 👍👎 buttons
  - On click: `POST /public/knowledge/articles/{article}/feedback` with helpful: true/false
  - Shows count of helpful/not helpful

**Related endpoints**:
- `GET /public/knowledge/categories` (category tree)
- `GET /public/knowledge/articles` (list, paginated)
- `GET /public/knowledge/articles/search?q=...` (search, full-text)
- `GET /public/knowledge/articles/{article}` (article detail)
- `POST /public/knowledge/articles/{article}/feedback` (thumbs up/down)

**Notes**:
- Language toggle: respects Accept-Language header but allows manual switch
- Feedback counts visible to viewers (shows "12 found this helpful")
- Search is bilingual (query matches both ar/en content)

---

## Navigation Map

- **Sidebar: Knowledge**
  - **Categories** → **Category Manager** (tree, edit/delete)
  - **Articles** → **Article List** (search, filter, state/visibility)
    - (click article) → **Article Editor** (rich text, publish workflow, version history)
  - **Public Help Center** → `/help` (search, browse, feedback)

---

**Next**: [08-workspace-notifications.md](08-workspace-notifications.md)
