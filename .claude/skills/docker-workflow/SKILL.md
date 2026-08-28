---
name: docker-workflow
description: استخدمه دايمًا قبل تنفيذ أي أمر artisan/composer/phpunit/pest. المشروع شغال بالكامل جوه Docker (PHP وMySQL في كونتينرات)، ومفيش PHP مثبت على الجهاز نفسه — أي أمر لازم يعدّي عن طريق docker exec.
---

# التنفيذ يكون دايمًا جوه Docker

## القاعدة
مفيش PHP، composer، أو أي حزمة Laravel مثبتة على الجهاز (host) مباشرة.
كل أمر لازم يتنفذ جوه الكونتينر `azm-php82` وهو شغال في مسار `/var/www/html/competition-crm`.

```bash
# ❌ غلط - هيفشل أو ينفذ بنسخة PHP غلط لو أصلاً موجودة
php artisan migrate
composer require foo

# ✅ صح
docker exec -w /var/www/html/competition-crm azm-php82 php artisan migrate
docker exec -w /var/www/html/competition-crm azm-php82 composer require foo
```

## MySQL
نفس الكلام، أي أمر SQL مباشر يعدّي عن طريق:
```bash
docker exec -i $(docker ps -qf name=azm-mysql8) mysql -uroot -proot support_crm_competition
```

## القاعدة الفعلية بدل التذكر كل مرة
استخدم أهداف الـ `Makefile` الجاهزة بدل ما تكتب `docker exec` كل مرة:
```bash
make artisan cmd="migrate"
make test
make lint
make sh          # يفتحلك shell جوه الكونتينر لو محتاج تجرب حاجة يدوي
```

## نقاط تحقق
- [ ] هل أي أمر PHP/composer اتنفذ عن طريق `docker exec` أو `make`، مش مباشرة على الجهاز؟
- [ ] هل `DB_HOST=database` في `.env` (اسم السيرفس جوه docker-compose)، مش `127.0.0.1`؟
- [ ] هل `APP_TIMEZONE=UTC`؟ (أي قيمة تانية بتسرّب توقيت السيرفر لحسابات الـ SLA)
