PHP_CONTAINER ?= azm-php82
MYSQL_CONTAINER ?= $(shell docker ps -qf "name=azm-mysql8")
APP_PATH ?= /var/www/html/competition-crm

DEXEC = docker exec -w $(APP_PATH) $(PHP_CONTAINER)

.PHONY: help artisan composer lint lint-fix test test-security hooks sh mysql

help: ## يعرض الأوامر المتاحة
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

artisan: ## make artisan cmd="migrate"
	$(DEXEC) php artisan $(cmd)

composer: ## make composer cmd="require foo/bar"
	$(DEXEC) composer $(cmd)

lint: ## PHPStan + فحص فورمات Pint (بدون تعديل)
	$(DEXEC) vendor/bin/pint --test
	$(DEXEC) vendor/bin/phpstan analyse --no-progress

lint-fix: ## تنسيق تلقائي بـ Pint
	$(DEXEC) vendor/bin/pint

test: ## كل التستات
	$(DEXEC) vendor/bin/pest

test-security: ## تستات الصلاحيات والـ Audit فقط
	$(DEXEC) vendor/bin/pest --testsuite=Security

hooks: ## يتأكد إن الـ hooks شغالة فعلاً (smoke test بسيط)
	bash scripts/test-hooks.sh

api-lint: ## Lint OpenAPI spec
	npm run api:lint

api-client: ## Generate TypeScript client from OpenAPI spec
	npm run api:client

sh: ## يفتح shell جوه كونتينر الـ PHP
	docker exec -it -w $(APP_PATH) $(PHP_CONTAINER) bash

mysql: ## يفتح mysql client على قاعدة المشروع
	docker exec -it $(MYSQL_CONTAINER) mysql -uroot -proot support_crm_competition
