#!/usr/bin/env bash
# ملف مشترك يتم عمل source له من باقي الـ hooks
# بيحول أي مسار على الجهاز (host) لمساره المكافئ جوه كونتينر الـ PHP

# عدّل القيم دي لو أسماء الكونتينرات عندك مختلفة
: "${PHP_CONTAINER:=azm-php82}"
: "${MYSQL_CONTAINER:=$(docker ps -qf name=azm-mysql8)}"
: "${CONTAINER_APP_PATH:=/var/www/html/competition-crm}"

dexec() {
  docker exec -w "$CONTAINER_APP_PATH" "$PHP_CONTAINER" "$@"
}

# بيحول مسار الملف من صيغته على الجهاز لمساره النسبي جوه المشروع
# مثال: /home/user/.../competition-crm/app/Foo.php -> app/Foo.php
project_relative_path() {
  local file="$1"
  local root
  root=$(git rev-parse --show-toplevel 2>/dev/null)
  if [ -n "$root" ] && [[ "$file" == "$root"/* ]]; then
    echo "${file#"$root"/}"
  else
    basename "$file"
  fi
}

container_running() {
  docker ps --format '{{.Names}}' 2>/dev/null | grep -qx "$PHP_CONTAINER"
}
