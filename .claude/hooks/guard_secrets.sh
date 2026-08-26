#!/usr/bin/env bash
# PreToolUse على Write|Edit — يمنع كتابة سر واضح داخل الكود

INPUT=$(cat)
CONTENT=$(echo "$INPUT" | python3 "$(dirname "${BASH_SOURCE[0]}")/json_field.py" 'tool_input.content||tool_input.new_string')
[ -z "$CONTENT" ] && exit 0

PATTERNS=(
  'AKIA[0-9A-Z]{16}'
  'sk-[a-zA-Z0-9]{20,}'
  '-----BEGIN (RSA|EC|OPENSSH) PRIVATE KEY-----'
  'password[[:space:]]*=[[:space:]]*["'"'"'][^"'"'"']{4,}["'"'"']'
  'secret[[:space:]]*=[[:space:]]*["'"'"'][^"'"'"']{8,}["'"'"']'
)

for p in "${PATTERNS[@]}"; do
  if echo "$CONTENT" | grep -Eiq "$p" 2>/dev/null; then
    echo "🚫 محظور: يبدو أن الملف فيه سر/مفتاح مكتوب صريح (نمط: $p)." >&2
    echo "حط القيمة في .env واقرأها بـ config()/env()." >&2
    exit 2
  fi
done

exit 0
