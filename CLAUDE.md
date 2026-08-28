# Support CRM — قواعد ثابتة

مشروع Laravel، منظمة واحدة فقط (لا يوجد multi-tenancy). شغال بالكامل جوه Docker.

## قبل أي أمر
كل أمر PHP/composer/artisan لازم يعدّي جوه الكونتينر: `docker exec -w /var/www/html/competition-crm azm-php82 ...`
أو استخدم `make` (شوف `Makefile`). التفاصيل في skill: `docker-workflow`.

## القواعد الجوهرية (مفصّلة في `.claude/skills/`)
- **صلاحيات**: تحقق بمفتاح صلاحية (`module.action.scope`) عن طريق Policy، أبدًا مقارنة اسم دور. (`permission-scope`)
- **API**: envelope موحد، pagination إجباري على أي list، IDs = UUID، timestamps UTC + offset. (`api-contract`)
- **ثنائية اللغة**: أي حقل إداري `{ar, en}`، ممنوع نص مكتوب مباشر. (`bilingual-i18n`)
- **Audit**: أي تعديل حساس (صلاحية/حالة/ملكية/بيانات عميل) لازم Audit entry. (`audit-trail`)
- **SLA**: أي حساب مدة يعدّي على `WorkingTimeService` المركزي، مش `now()->diffInMinutes()`. (`working-time-sla`)
- **رفع الملفات**: allowlist + تخزين خارج web root + فحص فيروسات. (`upload-security`)

## الطبقات (layering) داخل كل Domain
`app/Domains/{Domain}/{Actions,Data,Events,Http,Jobs,Listeners,Models,Policies,Services}`
منطق العمل في Action، التحقق في FormRequest، التفويض في Policy، شكل الخرج في Resource.
Controller يبقى thin: يستدعي Action ويرجع Resource.

## قبل ما تقول "خلصت"
راجع `.claude/skills/backend-definition-of-done/SKILL.md` وشغّل:
```
make lint && make test
```

## المرجع الكامل
- `docs/contracts/conventions-digest.md` — أسماء أعمدة، enums، قواعد ثابتة (اقرأه كامل، صغير ورخيص).
- `Support_CRM_Backend_First_Backlog.csv` — كل الـ User Stories بترتيب الاعتماديات.
