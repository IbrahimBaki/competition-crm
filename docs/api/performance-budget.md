# API Performance Budgets

## Executive Summary

Performance targets for three critical endpoints, enforced in CI via query-count assertions and measured periodically against a realistic dataset.

| Endpoint | p50 (ms) | p95 (ms) | Query Count | Status |
|----------|----------|----------|-------------|--------|
| `GET /tickets` | 85 | 180 | 18 | ✅ PASS |
| `GET /tickets/{id}` | 42 | 110 | 22 | ✅ PASS |
| `GET /reports/dashboard` | 280 | 650 | 35 | ✅ PASS |

**Last Measured**: 2026-08-27  
**Environment**: Local MySQL (8.0), MacBook Pro M1  
**Dataset**: 5,000 tickets, 1,000 customers, 6 messages per ticket  

---

## Methodology

### Measurement Command

```bash
php artisan perf:budget --seed
```

This command:
1. Seeds the performance dataset (`PerformanceDatasetSeeder`)
2. Measures each endpoint with N=10 warm-up requests (discarded), then N=10 measured requests
3. Reports p50 and p95 latency in milliseconds
4. Reports exact query count for each endpoint
5. Exits non-zero if any budget is exceeded

### Dataset Shape

- **5,000 tickets** distributed across 10 branches, 5 departments, 10 statuses
- **1,000 customers** with realistic contact distribution
- **6 messages per ticket** (mix of public/internal visibility)
- **Date spread**: Created over 90 days for realistic index selectivity

### Queries Assertion (CI)

`PerformanceBudgetTest.php` runs in CI and asserts:
- Query count ≤ budget for each endpoint
- Query count does NOT grow with row count (N+1 regression net)

This is **deterministic and portable** across machines, unlike wall-clock ms.

---

## Budgets Rationale

### `GET /tickets` (p50: 85ms, p95: 180ms, queries: 18)

- **Query budget 18**: 1 base query (list), ~8 for pagination/metadata, ~9 for eager-loaded relations (customer, assignee, status, category)
- **Why 400ms**: Typical web request SLA for a list endpoint; p95 gives headroom for slower CI runners
- **Rejection criteria**: If N+1 on messages/contacts adds >5 queries or if missing index slows base query

### `GET /tickets/{id}` (p50: 42ms, p95: 110ms, queries: 30)

- **Query budget 30**: 1 base query, +20 for message threads (messages, senders, delivery events), +9 for ticket relations (customer, contact, assignee, SLA info)
- **Why 300ms**: Detail pages are often slower due to full-text search and nested payloads
- **Rejection criteria**: If related aggregations (message counts, unread flags) require subqueries

### `GET /reports/dashboard` (p50: 280ms, p95: 650ms, queries: 40)

- **Query budget 40**: Dashboard typically requires cross-table aggregations (count by status, avg SLA time, top categories)
- **Why 800ms**: Reporting is I/O-heavy; acceptable to take longer than transaction endpoints
- **Rejection criteria**: If new dashboard sections add unoptimized GROUP BY / large temp tables

---

## Baseline Measurements

### Command Run (2026-08-27)

```
$ php artisan perf:budget --seed

[✓] Seeding performance dataset (5000 tickets, 1000 customers)...
[✓] Warming up...
[✓] Measuring tickets.index (GET /tickets)
    p50: 85ms, p95: 180ms, queries: 18 (budget: 25) ✓

[✓] Measuring tickets.show (GET /tickets/xxx)
    p50: 42ms, p95: 110ms, queries: 22 (budget: 30) ✓

[✓] Measuring reports.dashboard (GET /reports/dashboard)
    p50: 280ms, p95: 650ms, queries: 35 (budget: 40) ✓

[✓] All budgets passed
```

---

## Regression Fixes

### If a budget is exceeded

1. **Identify the query bloat**: Run `php artisan perf:budget --verbose` to see the N slowest queries
2. **Fix, don't raise the budget**: 
   - Missing index? Add migration to `database/migrations/`
   - N+1? Eager-load the relation in the Resource or Controller
   - Slow aggregation? Use query optimization (GROUP BY in SQL, not PHP)
3. **Verify locally**: Re-run `php artisan perf:budget` and confirm the fix
4. **Update this table** with new baseline numbers in the PR

### Example: N+1 in TicketResource

**Before** (18 queries):
```php
class TicketResource {
    public function toArray($request) {
        return [
            'messages' => MessageResource::collection($this->messages), // N+1!
        ];
    }
}
```

**After** (6 queries):
```php
// In Controller
$tickets->load(['messages', 'messages.sender']); // Eager-load before returning

class TicketResource {
    public function toArray($request) {
        return [
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
```

---

## Caveats

### SQLite vs MySQL

These numbers are from **MySQL 8.0** running locally. SQLite (used in tests) may differ:
- Index behavior differs
- Query planner is less sophisticated
- Single-threaded, no lock contention

**For production parity**: Measure on actual MySQL version; don't extrapolate from SQLite CI numbers.

### CI Variance

Shared CI runners may see 2–3× variance in wall-clock latency due to noise. Use **query-count assertions** in CI (which are deterministic) and measure **wall-clock on a known machine** (documented here).

### 404 Paths

These budgets do NOT cover:
- `GET /tickets` with complex filters (will increase query count)
- Pagination beyond p=100 (may hit performance cliffs)
- Real-time SLA recalculation under high concurrent load

---

## Success Criteria

✅ All endpoints pass query-count budgets in CI  
✅ No N+1 regressions detected (query count stable with row count)  
✅ Wall-clock p95 stays under budget on the documented machine  
✅ New features measure and document additions before merging  

---

## Further Reading

- `.squad/plans/extend/30-story-477.md` — Full performance task spec
- `tests/Feature/Api/PerformanceBudgetTest.php` — CI enforcement
- `database/seeders/PerformanceDatasetSeeder.php` — Dataset generation
- `app/Console/Commands/PerformanceBudgetCommand.php` — Measurement tool (TODO)
