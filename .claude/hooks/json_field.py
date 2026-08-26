#!/usr/bin/env python3
"""يقرأ JSON من stdin ويطبع حقل واحد. بديل عن jq عشان نضمن توفره في أي بيئة.
الاستخدام: python3 json_field.py tool_input.command
"""
import json
import sys

def get(obj, path):
    for key in path.split('.'):
        if isinstance(obj, dict) and key in obj:
            obj = obj[key]
        else:
            return ""
    return obj if isinstance(obj, str) else ""

if __name__ == "__main__":
    try:
        data = json.load(sys.stdin)
    except Exception:
        sys.exit(0)
    path = sys.argv[1] if len(sys.argv) > 1 else ""
    # يدعم مسارات بديلة مفصولة بـ || زي: tool_input.content||tool_input.new_string
    for p in path.split("||"):
        val = get(data, p)
        if val:
            print(val)
            break
