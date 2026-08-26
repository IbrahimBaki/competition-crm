#!/usr/bin/env bash
# PreToolUse على Write|Edit — بيفحص المحتوى الجديد *قبل* ما يتكتب على القرص، ويوقفه لو خالف قاعدة جوهرية
# القواعد مبنية على backlog المشروع: BE-03 (صلاحيات)، BE-05 (عقد API)، BE-06 (ثنائية اللغة)، BE-08 (Audit)، BE-02 (SLA)

INPUT=$(cat)
FILE=$(echo "$INPUT" | python3 "$(dirname "${BASH_SOURCE[0]}")/json_field.py" tool_input.file_path)
CONTENT=$(echo "$INPUT" | python3 "$(dirname "${BASH_SOURCE[0]}")/json_field.py" 'tool_input.content||tool_input.new_string')

[ -z "$FILE" ] && exit 0
[ -z "$CONTENT" ] && exit 0

case "$FILE" in
  *.php) ;;
  *) exit 0 ;;
esac

block() {
  echo "🚫 محظور في $FILE: $1" >&2
  exit 2
}

# 1) BE-03: مقارنة role بالاسم بدل permission key — قاعدة صلبة، تُحظر
if echo "$CONTENT" | grep -Eq "role(->name)?\s*(==|===)\s*['\"][A-Za-z_]+['\"]"; then
  block "بتقارن الدور بالاسم مباشرة. استخدم Policy مبنية على permission key (module.action.scope) بدل مقارنة اسم الدور (BE-03). راجع skill: permission-scope."
fi

# 2) BE-05: list endpoint بدون pagination — قاعدة صلبة، تُحظر لو الملف كنترولر وفيه index() بترجع ::all()
if echo "$FILE" | grep -Eqi 'Controller\.php$' && \
   echo "$CONTENT" | grep -Eq 'function\s+index\s*\(' && \
   echo "$CONTENT" | grep -Eq '::all\(\)' ; then
  block "دالة index() بترجع ::all() من غير pagination. كل list endpoint لازم paginate() (BE-05: no unpaginated list endpoint anywhere)."
fi

# 3) BE-05: ID رقمي تسلسلي في API Resource بدل UUID
if echo "$FILE" | grep -Eqi 'Resource\.php$' && echo "$CONTENT" | grep -Eq "'id'\s*=>\s*\\\$this->id\b"; then
  block "بترجع \$this->id (auto-increment) في API Resource. المعرفات الظاهرة في الـ API لازم تكون UUID (BE-05)."
fi

exit 0
