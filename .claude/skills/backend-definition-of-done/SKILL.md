---
name: backend-definition-of-done
description: استخدمه كقائمة فحص أخيرة قبل ما تعتبر أي User Story أو endpoint في الباك-إند "منتهي فعلاً". يجمع كل معايير الجودة من باقي الـ skills في تشيك ليست واحدة عملية.
---

# تعريف الانتهاء (Definition of Done) — مبني على BE-30

قبل ما تقول "خلصت" على أي story، راجع:

## العقد والتوثيق
- [ ] الـ endpoint موجود في OpenAPI doc (لا يوجد endpoint غير موثّق).
- [ ] OpenAPI بيتحقق منه في CI (validate ينجح).
- [ ] أي تغيير في العقد بعد التجميد إما إضافي (additive) أو نسخة جديدة صريحة (v2).

## الأمان والصلاحيات
- [ ] التحقق بمفتاح صلاحية، مش اسم دور (`permission-scope` skill).
- [ ] النطاق (own/team/department/any) مفروض على مستوى الـ query.
- [ ] مفيش سر مكتوب في الكود (`upload-security` skill).

## البيانات والعرض
- [ ] الحقول التي يكتبها الأدمن ثنائية اللغة، ومفيش نص hardcoded (`bilingual-i18n` skill).
- [ ] المعرّفات UUID، التواريخ ISO-8601 مع offset، والتخزين UTC.
- [ ] أي تغيير حساس مسجل في الـ Audit Trail (`audit-trail` skill).

## SLA والوقت
- [ ] أي حساب مدة يمر على `WorkingTimeService` المركزي (`working-time-sla` skill).

## الاختبار والأداء
- [ ] تستات تغطي الـ authorization scope، والـ SLA golden scenarios، وvisibility allowlist للبورتال.
- [ ] وقت الاستجابة قيس فعليًا على بيانات واقعية (مش افتراض).
- [ ] Idempotency-Key شغّال على أي endpoint إنشاء.

## قبل الرد النهائي لأي مهمة
شغّل عقليًا: "لو مراجع تقني قرأ الديف بتاعي دلوقتي، هل هيلاقي حاجة من القايمة دي ناقصة؟"
