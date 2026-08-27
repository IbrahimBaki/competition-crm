# دليل اختبار Postman الشامل — دورة العمل الكاملة

> **تاريخ التحديث:** 2026-08-27 | **الإصدار:** 1.0 | **المرجع:** Phases 0-7 (SLA + Automation + Watchers + AI + Reporting + Integrations)

---

## المحتويات
1. [المقدمة والإعداد](#المقدمة-والإعداد)
2. [جدول الأدوار والصلاحيات](#جدول-الأدوار-والصلاحيات)
3. [إنشاء مستخدمي اختبار](#إنشاء-مستخدمي-اختبار)
4. [الدورة الكاملة خطوة بخطوة](#الدورة-الكاملة-خطوة-بخطوة)
5. [اختبار الصلاحيات (Negative Testing)](#اختبار-الصلاحيات--negative-testing)
6. [ملحق: الأخطاء الشائعة](#ملحق-الأخطاء-الشائعة)

---

## المقدمة والإعداد

### 1.1 البيئة والسيرفر

**تشغيل السيرفر داخل Docker:**
```bash
docker-compose up -d
```

**تشغيل السيدرز (تعمير قاعدة البيانات):**
```bash
docker exec -w /var/www/html/competition-crm azm-php82 php artisan migrate:fresh --seed
```

يتم إنشاء:
- مستخدم إداري واحد (سيتم طلب كلمة السر من البيئة أو إنشاء واحدة عشوائية)
- 5 أدوار نظام: Administrator, Manager, Supervisor, Agent, Viewer
- فرعين جاهزين: HQ (ساعات عمل 9-5) و OPS24 (24/7)
- 3 فئات تذاكر: support, billing, support.software
- 6 حالات تذكرة: new, open, pending, resolved, closed, spam
- سياسة SLA افتراضية (Default SLA Policy)

### 1.2 إعداد Postman

**متغيرات البيئة (Environment Variables):**

```
base_url = http://localhost:8000/api/v1
staff_token = [سيتم ملأه من تسجيل الدخول]
portal_token = [للعملاء]
admin_email = admin@example.com
manager_email = manager+test@example.com
supervisor_email = supervisor+test@example.com
agent_email = agent+test@example.com
viewer_email = viewer+test@example.com
```

**Headers الأساسية (تطبقها على كل Request):**

```
Authorization: Bearer {{staff_token}}
Content-Type: application/json
Accept: application/json
Idempotency-Key: {{$guid}}  [على POST/PATCH/DELETE]
```

### 1.3 فهم Response Envelope

**النجاح (200/201/202):**
```json
{
  "data": { "id": "...", "name": "...", ... },
  "meta": {
    "request_id": "req_xyz",
    "page": 1,
    "per_page": 15,
    "total": 100,
    "total_pages": 7
  },
  "links": {
    "self": "...", "first": "...", "last": "...", "prev": "...", "next": "..."
  }
}
```

**الفشل (4xx/5xx):**
```json
{
  "error": {
    "code": "validation_failed",
    "message": "The given data was invalid.",
    "request_id": "req_xyz",
    "field_errors": {
      "email": ["The email field is required."]
    }
  }
}
```

---

## جدول الأدوار والصلاحيات

| الدور | الوصف | الصلاحيات الأساسية |
|------|-------|------------------|
| **Administrator** | مدير النظام | كل الصلاحيات (full access) |
| **Manager** | مدير القسم | إدارة الموارد، التذاكر، SLA، الأتمتة، التقارير |
| **Supervisor** | مشرف | إدارة تذاكر القسم/الفريق، الأتمتة، التقارير |
| **Agent** | موظف دعم | إنشاء/تعديل التذاكر الخاصة به، الرد، الـ AI |
| **Viewer** | مطّلع | عرض التذاكر الخاصة به والرسائل فقط (قراءة) |

---

## إنشاء مستخدمي اختبار

### الخطوة 1: تسجيل دخول الأدمن

**Request:**
```
POST {{base_url}}/auth/login
Body (JSON):
{
  "email": "admin@example.com",
  "password": "i5WLapgEehXliJ3N"  [من سطر تشغيل السيدرز]
}
```

**Response:**
```json
{
  "data": {
    "id": "uuid-1", "email": "admin@example.com", "name": "Admin User", ...
  },
  "meta": {
    "token": "1|abcdef..."  [احفظ هذا في staff_token]
  }
}
```

### الخطوة 2: إنشاء مستخدم لكل دور

#### أ. دعوة مستخدم جديد

**Request:**
```
POST {{base_url}}/users/invite
Authorization: Bearer {{staff_token}}
Body (JSON):
{
  "email": "manager+test@example.com"
}
```

**Response:**
```json
{
  "data": {
    "id": "inv-uuid-1", "email": "manager+test@example.com", 
    "expires_at": "2026-08-30T12:00:00Z"
  }
}
```

> **ملاحظة:** الـ raw token يُرسل في الإيميل فقط. **للاختبار السريع:**
> ```bash
> docker exec -w /var/www/html/competition-crm azm-php82 php artisan tinker
> >>> DB::table('user_invitations')->latest()->first()->token_hash
> ```
> ثم فك hash أو اعكس العملية بـ check، أو ببساطة أنشئ المستخدمين مباشرة:
> ```bash
> >>> $u = User::create(['email' => 'manager+test@example.com', 'name' => 'Manager', 'password' => bcrypt('password'), 'uuid' => Str::uuid()]);
> >>> $u->roles()->attach(Role::where('name', 'manager')->first());
> ```

#### ب. قبول الدعوة (الطريقة الرسمية)

بعد الحصول على الـ token من الإيميل:

**Request:**
```
POST {{base_url}}/invitations/{raw-token}/accept
Body (JSON):
{
  "name": "Manager Test",
  "password": "SecurePassword123!"
}
```

#### ج. تعيين الدور

أولاً، احصل على uuid الدور:

**Request:**
```
GET {{base_url}}/roles
Authorization: Bearer {{staff_token}}
```

**Response:** (يرجع جميع الأدوار)

ثم عيّن الدور على المستخدم الجديد:

**Request:**
```
POST {{base_url}}/roles/{role-uuid}/users/{new-user-uuid}
Authorization: Bearer {{staff_token}}
```

#### د. تسجيل دخول المستخدم الجديد والتحقق

**Request:**
```
POST {{base_url}}/auth/login
Body (JSON):
{
  "email": "manager+test@example.com",
  "password": "SecurePassword123!"
}
```

احفظ token في `manager_token` (في متغير منفصل أو في البيئة).

**تحقق من الصلاحيات:**
```
GET {{base_url}}/auth/me
Authorization: Bearer {{manager_token}}
```

**Response:** يجب أن تظهر `permission_keys` تحتوي على صلاحيات Manager (automation.rules.manage, sla.policies.view, إلخ).

---

## الدورة الكاملة خطوة بخطوة

### المرحلة أ: البنية التنظيمية (Administrator / Manager)

**1. عرض الأقسام المتزرعة:**
```
GET {{base_url}}/departments
Authorization: Bearer {{staff_token}}
```

في حالة عدم وجود أقسام، أنشئ واحدة:

**2. إنشاء قسم جديد:**
```
POST {{base_url}}/departments
Authorization: Bearer {{staff_token}}
Body (JSON):
{
  "branch_id": "1",
  "name": {
    "ar": "قسم الدعم الفني",
    "en": "Technical Support"
  },
  "code": "support-dept"
}
```

احفظ `id` من الرد.

**3. عرض الفروع:**
```
GET {{base_url}}/branches
Authorization: Bearer {{staff_token}}
```

(HQ و OPS24 موجودان بالفعل)

---

### المرحلة ب: العملاء (Supervisor/Agent)

**1. إنشاء عميل:**
```
POST {{base_url}}/customers
Authorization: Bearer {{staff_token}}
Body (JSON):
{
  "name": "شركة الحلول الذكية",
  "preferred_locale": "ar"
}
```

احفظ UUID من الرد.

**2. إضافة جهة اتصال:**
```
POST {{base_url}}/customers/{customer-id}/contacts
Authorization: Bearer {{staff_token}}
Body (JSON):
{
  "type": "email",
  "value": "contact@example.com",
  "label": "البريد الرسمي",
  "is_primary": true
}
```

**3. إضافة ملاحظة:**
```
POST {{base_url}}/customers/{customer-id}/notes
Authorization: Bearer {{staff_token}}
Body (JSON):
{
  "body": "عميل مهم، معايير خدمة عالية"
}
```

**4. عرض Timeline:**
```
GET {{base_url}}/customers/{customer-id}/timeline
Authorization: Bearer {{staff_token}}
```

---

### المرحلة ج: دورة التذكرة الكاملة (Agent/Supervisor)

#### 1. إنشاء تذكرة

**Request:**
```
POST {{base_url}}/tickets
Authorization: Bearer {{agent_token}}  [أو staff_token]
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "customer_uuid": "customer-uuid-from-step-b",
  "department_uuid": "department-id-from-step-a",
  "subject": "مشكلة في تسجيل الدخول",
  "body": "لا أستطيع تسجيل الدخول إلى الحساب",
  "priority": "high",
  "category_uuid": "support-uuid"  [من ticket-categories seeder: support]
}
```

احفظ `id` و `uuid` من الرد.

#### 2. عرض التذكرة

**Request:**
```
GET {{base_url}}/tickets/{ticket-uuid}
Authorization: Bearer {{agent_token}}
```

**تحقق من:**
- وجود قسم `sla` في الرد
- بدء SLA clocks تلقائيًا

#### 3. تغيير الأولوية (اختبر hooks)

**Request:**
```
PATCH {{base_url}}/tickets/{ticket-uuid}
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "priority": "urgent"
}
```

**تحقق من:**
- تحديث `sla` في الرد (يجب أن يتغير الـ target بناءً على الأولوية الجديدة)

#### 4. نقل لقسم آخر

**Request:**
```
POST {{base_url}}/tickets/{ticket-uuid}/transfer/department
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "department_uuid": "another-department-uuid"
}
```

**تحقق من:** تحديث SLA وتنفيذ الأتمتة (إن وجدت).

#### 5. تعيين التذكرة

**Request:**
```
POST {{base_url}}/tickets/{ticket-uuid}/assign
Authorization: Bearer {{supervisor_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "assignee_uuid": "agent-uuid"
}
```

#### 6. استلام التذكرة (claim)

بدلاً من التعيين، قد يستلمها الموظف مباشرة:

**Request:**
```
POST {{base_url}}/tickets/{ticket-uuid}/claim
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
```

#### 7. إرسال رسالة عادية

**Request:**
```
POST {{base_url}}/tickets/{ticket-uuid}/messages
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "body": "تم حل المشكلة، يرجى اتباع الخطوات التالية...",
  "channel": "email",
  "is_internal": false
}
```

#### 8. إضافة ملاحظة داخلية مع mentions (Phase 3)

**Request:**
```
POST {{base_url}}/tickets/{ticket-uuid}/messages
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "body": "@supervisor تفضل المراجعة",
  "channel": "internal",
  "is_internal": true,
  "mentions": ["supervisor-uuid"]
}
```

**تحقق من:**
- ظهور `mentions` array في الرد
- إرسال إشعارات (يمكن التحقق من `/notifications`)

#### 9. إضافة مراقب (Phase 3)

**Request:**
```
POST {{base_url}}/tickets/{ticket-uuid}/watchers
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "user_uuid": "manager-uuid"
}
```

**تحقق من:** ظهور `is_watched: true` و `watcher_count: 1` في الرد من `GET /tickets/{ticket-uuid}`.

#### 10. تغيير حالة التذكرة

**Request:**
```
POST {{base_url}}/tickets/{ticket-uuid}/status
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "status": "resolved"
}
```

**القيم المتاحة:** new, open, pending, resolved, closed, spam

#### 11. إعادة الفتح

**Request:**
```
POST {{base_url}}/tickets/{ticket-uuid}/reopen
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
```

---

### المرحلة د: SLA Management (Phase 1)

**1. عرض سياسات SLA:**
```
GET {{base_url}}/sla/policies
Authorization: Bearer {{staff_token}}
```

يجب أن تظهر "Default SLA Policy" مع أهداف first_response و resolution.

**2. عرض SLA التذكرة:**

قبل عرض تفاصيل التذكرة بالكامل، احصل على `sla_clocks` من الرد ثم:

```
GET {{base_url}}/tickets/{ticket-uuid}/sla/{clock-uuid}
Authorization: Bearer {{staff_token}}
```

**3. إعادة ضبط SLA:**

```
POST {{base_url}}/tickets/{ticket-uuid}/sla/{clock-uuid}/reset
Authorization: Bearer {{staff_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "reason": "تأخر الاستجابة للظروف القاهرة"
}
```

**تحقق من:** إضافة audit entry في `/audit-logs`.

---

### المرحلة ه: الأتمتة (Phase 2)

**1. إنشاء قاعدة أتمتة:**

```
POST {{base_url}}/automation/rules
Authorization: Bearer {{manager_token}}  [أو administrator]
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "key": "escalate-urgent",
  "name": {
    "ar": "تصعيد الحالات العاجلة",
    "en": "Escalate Urgent Cases"
  },
  "department_uuid": "dept-uuid",
  "trigger": "priority_changed_to_urgent",
  "conditions": [],
  "actions": [
    {
      "type": "notify",
      "recipients": ["supervisor-uuid"]
    }
  ],
  "priority": 1,
  "is_active": true
}
```

احفظ `uuid`.

**2. عرض تنفيذات الأتمتة:**

```
GET {{base_url}}/automation/executions
Authorization: Bearer {{manager_token}}
```

غيّر أولوية تذكرة ما على "urgent"، يجب أن تظهر execution جديدة.

---

### المرحلة و: الذكاء الاصطناعي (Phase 4)

**1. توليد ملخص:**

```
POST {{base_url}}/tickets/{ticket-uuid}/ai/summary
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
```

**Response:** إنشاء AiSuggestion في state "Pending".

**2. توليد رد مقترح:**

```
POST {{base_url}}/tickets/{ticket-uuid}/ai/suggested-reply
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
```

**3. عرض الاقتراحات:**

```
GET {{base_url}}/ai/suggestions
Authorization: Bearer {{agent_token}}
```

**4. حل الاقتراح (قبول/رفض):**

```
POST {{base_url}}/ai/suggestions/{suggestion-uuid}/resolve/accept
Authorization: Bearer {{agent_token}}
Idempotency-Key: {{$guid}}
```

أو `resolve/reject`.

**5. عرض الاستخدام:**

```
GET {{base_url}}/ai/usage
Authorization: Bearer {{agent_token}}
```

---

### المرحلة ز: قاعدة المعرفة (Knowledge Base)

**1. عرض التصنيفات:**

```
GET {{base_url}}/knowledge/categories
Authorization: Bearer {{staff_token}}
```

متزرعة مسبقًا: getting-started, faq, troubleshooting.

**2. إنشاء مقال:**

```
POST {{base_url}}/knowledge/articles
Authorization: Bearer {{staff_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "category_uuid": "getting-started-uuid",
  "title": "كيفية إعادة تعيين كلمة المرور",
  "title_ar": "كيفية إعادة تعيين كلمة المرور",
  "body": "# خطوات إعادة التعيين\n1. انقر على 'نسيت كلمة المرور'\n2. ...",
  "body_format": "markdown"
}
```

احفظ uuid.

**3. نشر المقال:**

```
POST {{base_url}}/knowledge/articles/{article-uuid}/state
Authorization: Bearer {{staff_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "state": "published"
}
```

**4. البحث:**

```
GET {{base_url}}/knowledge/articles/search?q=password
Authorization: Bearer {{staff_token}}
```

---

### المرحلة ح: التقارير والجدولة (Phase 5)

**1. عرض التقارير:**

```
GET {{base_url}}/reports
Authorization: Bearer {{staff_token}}
```

**2. تصدير تقرير:**

```
POST {{base_url}}/reports/{report-uuid}/export
Authorization: Bearer {{staff_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "format": "csv",
  "filters": {}
}
```

**3. إنشاء جدولة تقرير:**

```
POST {{base_url}}/report-schedules
Authorization: Bearer {{staff_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "report_uuid": "report-uuid",
  "name": {
    "ar": "تقرير الأداء الأسبوعي",
    "en": "Weekly Performance Report"
  },
  "frequency": "weekly",
  "day_of_week": 5,  [الجمعة]
  "hour": 9,
  "recipients": ["manager-email@example.com"]
}
```

---

### المرحلة ط: التكاملات (Phase 6)

**1. إنشاء webhook subscription:**

```
POST {{base_url}}/webhooks/subscriptions
Authorization: Bearer {{staff_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "event": "ticket.created",
  "url": "https://example.com/webhooks/ticket-created",
  "is_active": true
}
```

**2. عرض deliveries:**

```
GET {{base_url}}/webhooks/deliveries
Authorization: Bearer {{staff_token}}
```

---

### المرحلة ي: الإشعارات

**1. عرض الإشعارات:**

```
GET {{base_url}}/notifications
Authorization: Bearer {{agent_token}}
```

**2. تحديد كمقروء:**

```
POST {{base_url}}/notifications/{notification-uuid}/read
Authorization: Bearer {{agent_token}}
```

**3. تفضيلات الإشعارات:**

```
GET {{base_url}}/notifications/preferences
Authorization: Bearer {{agent_token}}
```

---

### المرحلة ك: بوابة العميل (Portal)

**1. تسجيل عميل جديد (اختياري):**

```
POST {{base_url}}/portal/auth/register
Body (JSON):
{
  "email": "customer@example.com",
  "password": "CustomerPassword123!",
  "name": "العميل"
}
```

**2. تسجيل دخول عميل:**

```
POST {{base_url}}/portal/auth/login
Body (JSON):
{
  "email": "customer@example.com",
  "password": "CustomerPassword123!"
}
```

احفظ `meta.token` في `portal_token`.

**3. عرض تذاكره:**

```
GET {{base_url}}/portal/tickets
Authorization: Bearer {{portal_token}}
```

**4. إرسال رسالة:**

```
POST {{base_url}}/portal/tickets/{ticket-uuid}/messages
Authorization: Bearer {{portal_token}}
Idempotency-Key: {{$guid}}
Body (JSON):
{
  "body": "أشكركم على المساعدة!"
}
```

---

## اختبار الصلاحيات (Negative Testing)

| السيناريو | الـ Endpoint | الـ Role | النتيجة المتوقعة |
|---------|------------|--------|-----------------|
| Viewer يحاول إنشاء تذكرة | `POST /tickets` | Viewer | 403 Unauthorized |
| Agent يحاول حذف تذكرة | `DELETE /tickets/{id}` | Agent | 403 (ممنوع) |
| Agent يحاول إنشاء قاعدة أتمتة | `POST /automation/rules` | Agent | 403 |
| Supervisor يحاول تعديل سياسة SLA | `PATCH /sla/policies/{id}` | Supervisor | 403 (Manager فقط) |
| Manager يحاول حذف دور | `DELETE /roles/{id}` | Manager | 403 (Admin فقط) |

---

## ملحق: الأخطاء الشائعة

| الكود | HTTP | المعنى | الحل |
|-----|------|-------|------|
| `validation_failed` | 422 | بيانات دخل غير صحيحة | تحقق من `field_errors` |
| `unauthenticated` | 401 | لا توجد token أو انتهت صلاحيتها | تسجيل الدخول مجددًا |
| `unauthorized` | 403 | الصلاحيات غير كافية | تحقق من دور المستخدم |
| `not_found` | 404 | المورد غير موجود | تحقق من UUID صحيح |
| `idempotency_key_conflict` | 409 | نفس الطلب تم تنفيذه من قبل | غيّر idempotency key |
| `rate_limited` | 429 | عدد الطلبات مرتفع جدًا | انتظر قبل إعادة المحاولة |
| `internal_error` | 500 | خطأ في السيرفر | راجع سجلات السيرفر |

---

## ملاحظات مهمة

1. **الـ Idempotency:** كل `POST`/`PATCH`/`DELETE` يتطلب `Idempotency-Key` header. استخدم GUID جديد لكل طلب.

2. **الـ Pagination:** كل قائمة (`/tickets`, `/customers`, إلخ) معها pagination افتراضي 15 سجل. استخدم `page` query param للتنقل.

3. **الأدوار والصلاحيات:** بعض الـ endpoints تحتاج صلاحية محددة (مثل `automation.rules.manage`). راجع جدول الأدوار أعلاه.

4. **Timestamps:** جميع التواريخ بصيغة ISO 8601 UTC. مثال: `2026-08-27T14:30:00Z`.

5. **UUIDs:** جميع المعرفات في النظام من نوع UUID (v4). تأكد من نسخهم بدقة.

---

**آخر تحديث:** 2026-08-27 | **الإصدار:** 1.0
