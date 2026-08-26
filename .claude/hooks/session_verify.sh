#!/usr/bin/env bash
# Stop hook — يشتغل لما Claude يعتبر إنه خلّص رده. يشغّل الفحوصات الأساسية على اللي اتغيّر فعلاً

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

container_running || exit 0

CHANGED=$(git status --porcelain 2>/dev/null | awk '{print $2}' | grep -E '\.php$')
[ -z "$CHANGED" ] && exit 0

echo "▶ فحص نهائي على الملفات المتغيّرة (lint)..." >&2
dexec vendor/bin/pint --test 2>&1 | tail -n 20 >&2

NEW_TODOS=$(git diff --unified=0 2>/dev/null | grep -E '^\+.*\b(TODO|FIXME|XXX)\b' | wc -l | tr -d ' ')
[ "$NEW_TODOS" != "0" ] && echo "ℹ️ فيه $NEW_TODOS من TODO/FIXME جديدة، تأكد إنها مقصودة." >&2

exit 0
