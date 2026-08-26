#!/usr/bin/env bash
# PreToolUse على Write|Edit

INPUT=$(cat)
FILE=$(echo "$INPUT" | python3 "$(dirname "${BASH_SOURCE[0]}")/json_field.py" tool_input.file_path)
[ -z "$FILE" ] && exit 0

block() { echo "🚫 محظور تعديل هذا الملف: $1" >&2; exit 2; }

case "$FILE" in
  *.env|*.env.*|*/secrets.yaml|*id_rsa*|*.pem|*.key)
    block "ملف بيئة/مفاتيح سرية. عدّل .env.example ووثّق التغيير المطلوب بدل التعديل المباشر."
    ;;
  */vendor/*|*/node_modules/*)
    block "مكتبة خارجية. أي تعديل هنا هيضيع مع أول composer/npm install."
    ;;
  */docker/environment-setup.md)
    # ملف مرجعي بيوصف الـ Docker setup، تعديله يحتاج مراجعة يدوية وليس تلقائي
    echo "⚠️ بتعدّل ملف توثيق البيئة الأساسي. تأكد إن التعديل متعمّد." >&2
    ;;
esac

# migrations اتعمل لها commit قبل كده = لا تُعدَّل، أضف migration جديدة بدل منها
if echo "$FILE" | grep -Eiq '/database/migrations/.*\.php$'; then
  if git log --oneline -- "$FILE" 2>/dev/null | grep -q .; then
    block "هذا الـ migration اتعمله commit قبل كده. أضف migration جديد للتعديل بدل تعديل القديم، عشان ما يتكسرش على أي DB شغالة."
  fi
fi

exit 0
