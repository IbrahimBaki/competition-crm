---
name: audit-trail
description: استخدمه عند كتابة أي دالة بتغيّر بيانات حساسة (صلاحيات، ملكية تذكرة، حالة، بيانات عميل، إعدادات أمنية). يفرض تسجيل audit entry غير قابل للتعديل أو الحذف.
---

# سجل التدقيق (Audit Trail) — مبني على BE-08

## الفرق المهم
- **Audit entry** = دليل قانوني/امتثال (مين غيّر إيه) → **لا يُعدَّل ولا يُحذف أبدًا**، حتى من الأدمن.
- **Log عادي** = تشخيص أعطال فقط، بيحتوي correlation/request id.
دول شيئين منفصلين، متلخبطش بينهم.

## متى تسجّل Audit entry (إلزامي)
أي عملية من دول لازم تسجل: **actor, action, target, before, after, timestamp**
- تغييرات أمنية (تفعيل/تعطيل 2FA، تغيير صلاحيات)
- تغيير ملكية تذكرة (assignment)
- تغيير حالة (status transition)
- إعادة ضبط SLA
- أي تعديل على بيانات عميل (خصوصًا anonymize/block)
- تغييرات الهيكل التنظيمي (فرع/قسم/فريق)

## نمط الاستخدام
```php
AuditLog::record(
    actor: $currentUser,
    action: 'ticket.status_changed',
    target: $ticket,
    before: ['status' => $oldStatus],
    after:  ['status' => $newStatus],
);
```

## نقاط تحقق قبل اعتبار أي دالة تعديل "خلصت"
- [ ] لو الدالة بتعدّل حالة/ملكية/صلاحية → هل فيها استدعاء `AuditLog::record`؟
- [ ] هل الـ audit entries محمية من أي مسار حذف/تعديل في الكود (مفيش `update()`/`delete()` على موديل الـ Audit)؟
- [ ] هل عملية "المسح/الأرشفة" (retention purge) نفسها بتتسجل في الـ audit، والـ audit records مستثناة من الـ purge داخل نافذة الاحتفاظ بتاعتها؟
