#!/usr/bin/env bash
# PostToolUse على Write|Edit — بينسّق ويحلّل الملف عن طريق الأدوات المثبتة *جوه كونتينر PHP*
# مش على الجهاز، لأن Laravel والـ vendor شغالين جوه Docker (azm-php82)

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

INPUT=$(cat)
FILE=$(echo "$INPUT" | python3 "$(dirname "${BASH_SOURCE[0]}")/json_field.py" tool_input.file_path)
[ -z "$FILE" ] || [ ! -f "$FILE" ] && exit 0

case "$FILE" in
  *.php) ;;
  *.ts|*.tsx|*.js|*.jsx) exit 0 ;; # الفرونت React، بيتعامل معاه npm على الجهاز مباشرة عادةً — عدّل لو غير كده
  *) exit 0 ;;
esac

container_running || { echo "ℹ️ كونتينر $PHP_CONTAINER مش شغال، اتخطى الـ lint." >&2; exit 0; }

REL=$(project_relative_path "$FILE")

dexec vendor/bin/pint "$REL" >/dev/null 2>&1

OUT=$(dexec vendor/bin/phpstan analyse "$REL" --no-progress --error-format=raw 2>&1)
if echo "$OUT" | grep -qi "\.php:"; then
  echo "⚠️ PHPStan (level من phpstan.neon) لقى ملاحظات في $REL:" >&2
  echo "$OUT" | tail -n 30 >&2
fi

exit 0
