#!/usr/bin/env bash
# smoke test بسيط للـ hooks: يبعتلها JSON مزيّف زي اللي Claude Code بيبعته، ويتأكد إنها بترفض (exit 2)
set -u
PASS=0; FAIL=0
DIR=".claude/hooks"

check_blocks() {
  local desc="$1" hook="$2" json="$3"
  echo "$json" | bash "$DIR/$hook" >/dev/null 2>&1
  if [ "$?" -eq 2 ]; then
    echo "✅ $desc"; PASS=$((PASS+1))
  else
    echo "❌ $desc (المفروض يحظر ومحظرش)"; FAIL=$((FAIL+1))
  fi
}

check_allows() {
  local desc="$1" hook="$2" json="$3"
  echo "$json" | bash "$DIR/$hook" >/dev/null 2>&1
  if [ "$?" -eq 0 ]; then
    echo "✅ $desc"; PASS=$((PASS+1))
  else
    echo "❌ $desc (المفروض يسمح ومنعش)"; FAIL=$((FAIL+1))
  fi
}

# زي check_blocks بس بيحقن APP_ENV وهمي عشان يختبر الحظر برة local من غير ما يلمس .env الحقيقي
check_blocks_env() {
  local desc="$1" hook="$2" fake_env="$3" json="$4"
  echo "$json" | APP_ENV="$fake_env" bash "$DIR/$hook" >/dev/null 2>&1
  if [ "$?" -eq 2 ]; then
    echo "✅ $desc"; PASS=$((PASS+1))
  else
    echo "❌ $desc (المفروض يحظر ومحظرش)"; FAIL=$((FAIL+1))
  fi
}

check_allows_env() {
  local desc="$1" hook="$2" fake_env="$3" json="$4"
  echo "$json" | APP_ENV="$fake_env" bash "$DIR/$hook" >/dev/null 2>&1
  if [ "$?" -eq 0 ]; then
    echo "✅ $desc"; PASS=$((PASS+1))
  else
    echo "❌ $desc (المفروض يسمح ومنعش)"; FAIL=$((FAIL+1))
  fi
}

check_blocks "rm -rf / يتحظر" guard_dangerous_bash.sh \
  '{"tool_input":{"command":"rm -rf /"}}'

check_blocks_env "migrate:fresh برة local يتحظر" guard_dangerous_bash.sh "production" \
  '{"tool_input":{"command":"php artisan migrate:fresh"}}'

check_allows_env "migrate:fresh جوه local يتسمح" guard_dangerous_bash.sh "local" \
  '{"tool_input":{"command":"php artisan migrate:fresh"}}'

check_blocks "git push --force على main يتحظر" guard_dangerous_bash.sh \
  '{"tool_input":{"command":"git push --force origin main"}}'

check_allows "أمر عادي يتسمح" guard_dangerous_bash.sh \
  '{"tool_input":{"command":"php artisan route:list"}}'

check_blocks "تعديل .env يتحظر" guard_protected_paths.sh \
  '{"tool_input":{"file_path":".env"}}'

check_blocks "سر AWS مكتوب صريح يتحظر" guard_secrets.sh \
  '{"tool_input":{"content":"$key = \"AKIAABCDEFGHIJKLMNOP\";"}}'

check_blocks "مقارنة role بالاسم تتحظر" enforce_code_conventions.sh \
  '{"tool_input":{"file_path":"app/Http/Controllers/X.php","content":"if ($user->role->name === \"admin\") {}"}}'

check_blocks "index() من غير pagination تتحظر" enforce_code_conventions.sh \
  '{"tool_input":{"file_path":"app/Domains/Ticketing/Http/Controllers/TicketController.php","content":"function index(){ return Ticket::all(); }"}}'

echo ""
echo "النتيجة: $PASS نجح، $FAIL فشل"
[ "$FAIL" -eq 0 ]
