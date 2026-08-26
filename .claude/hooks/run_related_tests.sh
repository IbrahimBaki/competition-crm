#!/usr/bin/env bash
# PostToolUse على Write|Edit — يدوّر على تست مرتبط بالملف ويشغّله بـ Pest جوه الكونتينر

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

INPUT=$(cat)
FILE=$(echo "$INPUT" | python3 "$(dirname "${BASH_SOURCE[0]}")/json_field.py" tool_input.file_path)
[ -z "$FILE" ] || [ ! -f "$FILE" ] && exit 0

case "$FILE" in
  *.php) ;;
  *) exit 0 ;;
esac

container_running || exit 0

BASENAME=$(basename "$FILE")
NAME_NO_EXT="${BASENAME%.*}"

CANDIDATE=$(find tests -type f -iname "${NAME_NO_EXT}Test.php" 2>/dev/null | head -n1)
[ -z "$CANDIDATE" ] && exit 0

OUT=$(dexec vendor/bin/pest "$CANDIDATE" 2>&1)
echo "$OUT" | tail -n 40 >&2
echo "$OUT" | grep -qiE 'FAILED|failures' && echo "❌ التست $CANDIDATE فشل بعد التعديل." >&2

exit 0
