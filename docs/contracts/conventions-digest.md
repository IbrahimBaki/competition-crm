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

## Security Baseline (BE-09)
- **Validation:** كل write endpoint مع FormRequest صريح؛ mass assignment مقفول بـ `$guarded = ['*']` أو `$fillable` صريح على كل Model، مفروض بـ architecture test.
- **Secrets:** جميع المفاتيح والـ tokens من `config/*` + environment فقط، لا حرفي في الـ repo، ولا عودة من أي endpoint أو Resource.
- **Uploads:** `AttachmentRules` يفرض max size + MIME/extension allowlist؛ التخزين على private disk خارج web root بـ UUID key؛ اسم الملف الأصلي metadata فقط.
- **Scanning:** كل ملف يبدأ `pending` (quarantined)؛ job على الـ queue يضيّ `clean`/`infected`؛ `pending` download → HTTP 409 `attachment.scan_pending`؛ `infected` → 422 `attachment.scan_failed` والـ bytes محذوفة.
- **Anonymous endpoints:** مجموعة `/api/v1` من `auth/login`, `auth/two-factor/challenge`, `auth/password/*`, `invitations/*/accept` تحت `throttle:public` + `bot.protect` middleware؛ health probes (`/health/live`, `/health/ready`) خارج bot protection.
- **Attachment authorization:** كل download عبر `AttachmentPolicy::download`؛ UUID معروف لكن غير مصرح → 404 `not_found`، ليس 403 (لا تفشي الملكية).
