# 10. Reporting

**Domains**: Reporting  
**Surface**: Staff CRM only  
**Permissions**: `reports.view.*`, `reports.export.any`, `reports.schedule.manage`

---

## Reports List Screen

**Route**: `/crm/reports` or `/reports`  
**Purpose**: Browse available reports  
**Permission**: `reports.view.{own|department|branch|any}` (determines visible reports)  

**Available Reports** (grid or list):
| Report | Description | Type | Last Run | Actions |
|---|---|---|---|---|
| Dashboard | Key metrics, SLA summary, recent activity | Standard | Today | [View] |
| Ticket Trends | Volume, resolution time, SLA compliance by date | Trend | Yesterday | [View] |
| Agent Performance | Tickets handled, average resolution time, customer satisfaction per agent | Performance | 2026-08-26 | [View] |
| Customer Satisfaction | CSAT ratings, NPS, feedback summary | Survey | 2026-08-25 | [View] |
| SLA Compliance | Percentage of tickets meeting SLA targets | Compliance | 2026-08-27 | [View] |

**Filters** (per report visibility):
- Scope (own, department, branch, any) — based on `reports.view.*` permission keys
- Date range (quick presets: today, this week, this month, last 90 days)

**Related endpoints**:
- `GET /reports` (list available reports)

---

## Dashboard (Primary Report)

**Route**: `/crm/dashboard` or `/reports/dashboard`  
**Purpose**: Executive summary of key metrics  
**Permission**: `reports.view.{own|department|branch|any}`  

**Layout** (responsive grid):

### Row 1: KPI Cards (quick metrics)
```
┌────────────────┬────────────────┬────────────────┬────────────────┐
│ Open Tickets   │ Avg Resolution │ SLA Compliance │ Customer CSAT  │
│      45        │     2.4 days   │      92%       │      4.3/5     │
│ ↑ 3 from yesterday               │ ⚠️ 1 breach today               │
└────────────────┴────────────────┴────────────────┴────────────────┘
```

### Row 2: SLA Summary (chart)
```
SLA Status This Week
├─ Met: 152 tickets (85%)    [████████░░░░░░] Green
├─ Warning: 18 tickets (10%) [██░░░░░░░░░░░░] Yellow
└─ Breached: 9 tickets (5%)  [█░░░░░░░░░░░░░] Red
```

### Row 3: Charts (TBD per analytics needs)
- **Tickets Created vs Resolved** (trend line chart)
- **Tickets by Status** (pie chart: new/open/pending/resolved/closed)
- **Tickets by Priority** (bar chart: low/normal/high/urgent)

### Row 4: Recent Activity
- Last 10 tickets created
- Last 5 tickets resolved
- Recent SLA breaches

### Row 5: Agent Performance (if `reports.view.branch` or higher)
- Table: Agent name, tickets handled, avg resolution time, customer satisfaction

**Controls**:
- Date range picker (quick presets + custom range)
- Scope filter (own, department, branch, any) — if multiple available
- Refresh button (data typically cached, refresh on demand)
- [Export] button → export modal

**Performance Note**:
- Dashboard query budget: p95 ~650ms (per perf budget) — expect loading skeleton for ~500ms
- Implement staged rendering: KPI cards load first, charts second, detailed tables last

**Related endpoints**:
- `GET /reports/dashboard` (fetch all dashboard data)

---

## Report Detail Screen

**Route**: `/crm/reports/{report-name}` or `/crm/reports/ticket-trends`  
**Purpose**: Deep-dive into a specific report type  
**Permission**: Based on scope + report type  

**Report: Ticket Trends**
- **Chart**: Tickets created/resolved/pending over time (line chart, by day)
- **Filters**: Date range, category, priority, department, agent
- **Table**: Detail rows below chart (drill-down data)
- **Actions**: [Export], [Download PDF], [Schedule Report]

**Report: Agent Performance**
- **Table**: Agent, tickets handled, avg resolution time, customer CSAT, SLA compliance %
- **Sort**: by any column
- **Filters**: Date range, department, team
- **Actions**: [Export], [Email Results], [Schedule Report]

**Report: Customer Satisfaction**
- **Summary**: Avg CSAT rating, NPS score
- **Chart**: Rating distribution (histogram: 1⭐ / 2⭐ / 3⭐ / 4⭐ / 5⭐)
- **Comments**: Sample feedback comments (searchable, filterable by rating)
- **Actions**: [Export]

**Generic Report Controls** (all reports):

| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Export | Show export modal (format + async) | (see Export Modal below) | `reports.export.any` | No |
| Schedule | Show schedule modal | (see Schedule Report below) | `reports.schedule.manage` | No |
| Refresh | Re-fetch data | (GET same report endpoint) | (same) | Yes |

**Related endpoints**:
- `GET /reports/{report-name}?filter[date_from]=...&filter[date_to]=...` (report data with filters)

---

## Export Modal

**Route**: Modal, accessible from any report view  
**Purpose**: Export report data to file  
**Permission**: `reports.export.any`  

**Modal**:
```
Export Report: Ticket Trends

Format: [CSV ▼] (CSV, XLSX, PDF)
Range: [This Month ▼]
Include: ☑ Charts
         ☑ Summary Statistics

[Export] [Cancel]
```

**Export Behavior**:
- **Small reports** (< 5000 rows): synchronous, downloads immediately
- **Large reports** (>= 5000 rows): asynchronous
  - Show modal: "Export queued. You'll receive a download link via email."
  - Background job processes
  - User receives email notification with download link (1-week retention)
  - Link also appears in [Recent Exports] list on reports page

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Export | POST export | `POST /reports/{report}/export` (with format, date range, etc.) | (same) | No |

**Related endpoints**:
- `POST /reports/{report}/export` (initiate export)
- (Async job: `ReportExport` model tracks state: pending → running → ready → failed)

**Notes**:
- Exported files retained for 7 days (per config)
- Format validation: CSV, XLSX (max 1M rows), PDF (max 100 pages)
- Redaction: respects AI privacy setting (PII may be masked in exports)

---

## Schedule Report

**Route**: Modal, accessible from report view  
**Purpose**: Configure recurring report delivery  
**Permission**: `reports.schedule.manage`  

**Modal**:
```
Schedule Report: Dashboard

Report: Dashboard
Frequency: [Monthly ▼] (Daily, Weekly, Monthly)
Recipients (up to 25):
  ✓ me@company.com
  ✓ manager@company.com
  [ + Add Recipient]

Date/Time: [First day of month, 8:00 AM ▼]

[Save Schedule] [Cancel]
```

**Fields**:
| Field | Type | Required | Notes |
|---|---|---|---|
| Name | string | Yes | "Dashboard - Weekly" |
| Frequency | enum | Yes | daily, weekly, monthly |
| Day/Time | time picker | Yes | When to send (respects branch timezone?) |
| Recipients | multi-select (email) | Yes | Max 25 recipients |
| Format | enum | No | CSV, XLSX, PDF (default: PDF) |

**Actions**:
| Label | Action | Endpoint | Permission | Idempotent |
|---|---|---|---|---|
| Save Schedule | POST schedule | `POST /report-schedules` (or PATCH if editing) | (same) | No |

**Schedule Management List** (under Reports):

| Report | Frequency | Recipients | Next Run | Actions |
|---|---|---|---|---|
| Dashboard | Weekly | me@..., manager@... | 2026-09-01 | Edit / Delete |
| Ticket Trends | Monthly | me@... | 2026-09-01 | — |

**Related endpoints**:
- `POST /report-schedules`, `PATCH /report-schedules/{schedule}`, `GET /report-schedules`, `DELETE /report-schedules/{schedule}`

**Notes**:
- Scheduled reports sent via email as attachment (PDF or spreadsheet)
- Recipient limit: max 25 per schedule (to prevent spam)
- Admin can see all org schedules; users see only their own (unless admin permission)

---

## Navigation Map

- **Sidebar: Reports**
  - **Reports List** → (browse available reports)
    - (click report) → **Report Detail** (charts, filters, data table)
      - [Export] → **Export Modal** (format, async if large)
      - [Schedule] → **Schedule Modal** (frequency, recipients)
  - **Schedules** → (list of recurring reports, edit/delete)
- **Dashboard** (shortcut, main entry point for most users)

---

**Next**: [11-integrations.md](11-integrations.md)
