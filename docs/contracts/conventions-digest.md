# Conventions Digest — اقرأه كامل قبل أي تخطيط لأي Story

## بنية عامة
- منظمة واحدة فقط. لا يوجد `tenant_id` ولا عزل بين مؤسسات.
- كل جدول له `id` (UUID، عمود `binary(16)` أو `char(36)` حسب القرار المعتمد في `docs/contracts/api-contract.md`) — الـ API لا يرجّع أبدًا auto-increment.
- كل الأوقات UTC في القاعدة، تحويل العرض فقط عند الحاجة باستخدام timezone الفرع (`Branch.timezone`).
- أي حقل بيكتبه أدمن (اسم، وصف، label) = عمودين أو JSON `{ar, en}`، وليس عمود نصي واحد. اقرأ `docs/contracts/localization.md` كاملاً.

## الصلاحيات (BE-03)
- صيغة المفتاح: `{module}.{action}[.{scope}]` — الأمثلة: `tickets.view.department`, `customers.merge.any`.
- `Policy` واحدة لكل Model تتحقق من المفتاح، أبدًا مقارنة `role->name`.
- `GET /auth/me` يرجّع `permission_keys: []`، أبدًا `role_name`.

## عقد الـ API (BE-05)
- Envelope نجاح: `{ data, meta?, links? }`. Envelope فشل: `{ error: { code, message, field_errors?, request_id } }`.
- كل list: `page`, `per_page` (default 25, max 100)، `sort`, `filter[field]`, `filter[q]`, `include`.
- إنشاء الموارد يدعم `Idempotency-Key`.

## SLA (BE-02)
- كل حساب مدة عن طريق `App\Domains\Sla\Services\WorkingTimeService` فقط.
- الفرع له: `timezone` (IANA)، جدول أسبوعي، تقويم إجازات، أو علم `is_24_7`.

## Audit (BE-08)
- `App\Domains\Security\Services\AuditLogger::record(actor, action, target, before, after)`
- جدول الـ Audit بلا `update`/`delete` route على الإطلاق — Append-only.

## الـ Domains المعتمدة (لا تنشئ Domain جديد بدون داعي)
`Organisation` (فروع/أقسام/فرق) · `Security` (صلاحيات/مصادقة/Audit) ·
`Customers` · `Ticketing` · `Channels` · `Sla` · `Automation` ·
`Knowledge` · `Ai` · `Reporting` · `CustomerPortal`

## قبل ما تكتب أي endpoint جديد
ابحث عنه أولاً في `docs/contracts/api-contract.md` (لو موجود) بـ `grep` على الـ path — لا تقرأ الملف كامل لو كبير.
