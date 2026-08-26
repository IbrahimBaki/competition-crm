#!/usr/bin/env bash
# PreToolUse على Bash — نفس منطق قبل كده + قواعد خاصة بكونك شغال جوه Docker على مشروع Laravel واحد بدون tenancy

INPUT=$(cat)
CMD=$(echo "$INPUT" | python3 "$(dirname "${BASH_SOURCE[0]}")/json_field.py" tool_input.command)
[ -z "$CMD" ] && exit 0

block() { echo "🚫 محظور: $1" >&2; exit 2; }

APP_ENV_VALUE="${APP_ENV:-$(grep -E '^APP_ENV=' .env 2>/dev/null | cut -d= -f2 | tr -d '\r"')}"

# أوامر Artisan مدمّرة على القاعدة، مسموحة بس لو APP_ENV=local صراحة
echo "$CMD" | grep -Eiq 'artisan\s+(migrate:fresh|migrate:reset|db:wipe)' && {
  [ "$APP_ENV_VALUE" != "local" ] && \
    block "$(echo "$CMD" | grep -Eio 'migrate:fresh|migrate:reset|db:wipe') بيمسح كل الداتا. APP_ENV مش local دلوقتي (القيمة: '${APP_ENV_VALUE:-غير معروف}'). لو فعلاً local، تأكد من .env."
}

echo "$CMD" | grep -Eiq 'rm\s+-rf\s+(/|~|\*|\.\.|/var/www|/etc)' && \
  block "rm -rf على مسار واسع ممنوع دايمًا، سواء local أو مش local."

echo "$CMD" | grep -Eiq 'git\s+push\s+.*--force' && \
  echo "$CMD" | grep -Eiq '(main|master|prod|production)' && \
  block "git push --force على main/production ممنوع."

echo "$CMD" | grep -Eiq '(drop\s+database|drop\s+table)' && \
  ! echo "$CMD" | grep -Eiq 'support_crm_competition_test' && \
  block "DROP مباشر على قاعدة بيانات. استخدم migration بها down() واضح."

echo "$CMD" | grep -Eiq 'chmod\s+-R\s+777' && \
  block "chmod 777 خطر أمني."

exit 0
