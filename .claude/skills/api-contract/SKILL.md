---
name: api-contract
description: استخدمه في أي endpoint جديد أو تعديل على response/error/pagination. يفرض عقد API واحد ثابت (envelope, error catalog, pagination, idempotency) بدل ما كل مطور يخترع شكله.
---

# عقد الـ API الموحد (مبني على BE-05)

## قواعد إلزامية عند كتابة أي controller/endpoint:

1. **Envelope واحد للنجاح**: كل response ناجح يرجع بنفس الشكل الموحد. أي collection لازم تحتوي `meta` (pagination) و`links`. **ممنوع نهائيًا** أي list endpoint من غير pagination.

2. **Envelope واحد للفشل**: كل خطأ يحتوي `message`, `code` (enum ثابت وليس نص حر), `field_errors` (اختياري), و`request_id` (نفس قيمة header `X-Request-Id`). العميل (frontend) لازم يفرّع منطقه على `code` فقط، أبدًا مش على نص `message`.

3. **Pagination إلزامي**: `page`, `per_page` (default 25, max 100 — أي رقم أعلى يرجع 422)، `sort` من whitelist محدد، `filter[field]`, `filter[q]`, `include` من whitelist.

4. **Idempotency**: أي endpoint بينشئ موارد (POST) لازم يقبل header `Idempotency-Key` ويرجع نفس الـ response القديم لو اتكرر خلال 24 ساعة، بدل ما ينشئ نسخة تانية.

5. **المعرّفات و التواريخ**: كل ID ظاهر في الـ API لازم يكون UUID (مش auto-increment رقمي). كل timestamp لازم يكون ISO-8601 مع offset صريح، ومخزّن UTC، ويتحول للـ timezone بتاع الفرع (branch) بس عند العرض.

6. **Rate limiting**: لكل سياق مصادقة، مع إرجاع `Retry-After` عند التجاوز.

## قبل ما تعتبر أي endpoint "خلص":
- [ ] هل الـ response بيتبع الـ envelope الموحد؟
- [ ] هل فيه pagination لو ده list؟
- [ ] هل الأخطاء بترجع code من الـ enum، مش نص حر؟
- [ ] هل الـ IDs UUID؟
- [ ] هل الـ endpoint مضاف في OpenAPI doc (مصدر الحقيقة الوحيد، BE-30)؟
