#!/usr/bin/env bash
#
# .claude/hooks/remind_gap_logging.sh
#
# PostToolUse hook on AskUserQuestion: fires right after Claude asks the user
# a question mid-story (a blocking gap, backend bug, or design decision).
# Injects a reminder that once the user answers, Claude must record a dated
# Q&A-and-resolution entry in:
#
#   .squad/gaps/<plan_number>-<story_id>.md
#
# following the existing convention in that directory (see e.g.
# .squad/gaps/31-478.md, .squad/gaps/27-474.md): a
# "# Story <N> - Gaps & Backend Issues" heading, then one section per issue
# with Found / Asked / Decided / Done sub-parts.
#
# plan_number/story_id come from the same per-session state file
# track_current_story.sh writes on UserPromptSubmit
# (.squad/state/current_story-<session_id>.json) — so the target filename
# here always matches what verify_story_completion.sh (the Stop hook)
# already expects.
#
# Fails open: no state file (no story in flight) or unreadable input just
# means no reminder is injected — this hook never blocks the question.

set -uo pipefail

REPO_ROOT="$(pwd)"
STATE_DIR="${REPO_ROOT}/.squad/state"
LOG_FILE="${REPO_ROOT}/.claude/hooks/.state/gap-reminder.log"

mkdir -p "$(dirname "${LOG_FILE}")"

log() {
    echo "$(date -u +'%Y-%m-%dT%H:%M:%SZ') $*" >> "${LOG_FILE}"
}

INPUT_JSON="$(cat || true)"

SESSION_ID="$(python3 - "$INPUT_JSON" <<'PY'
import json, sys
try:
    data = json.loads(sys.argv[1] or "{}")
    print(data.get("session_id", "unknown"))
except Exception:
    print("unknown")
PY
)"

STATE_FILE="${STATE_DIR}/current_story-${SESSION_ID}.json"

if [[ ! -f "${STATE_FILE}" ]]; then
    log "SKIP session=${SESSION_ID} reason=no-current-story-state"
    exit 0
fi

read -r PLAN_NUMBER STORY_ID <<PYOUT
$(python3 - "${STATE_FILE}" <<'PY'
import json, sys
try:
    with open(sys.argv[1], "r", encoding="utf-8") as f:
        data = json.load(f)
    print(data.get("plan_number", "00"), data.get("story_id", "000"))
except Exception:
    print("00", "000")
PY
)
PYOUT

if [[ "${STORY_ID}" == "000" ]]; then
    log "SKIP session=${SESSION_ID} reason=no-story-id"
    exit 0
fi

GAP_FILE=".squad/gaps/${PLAN_NUMBER}-${STORY_ID}.md"

python3 - "${GAP_FILE}" "${STORY_ID}" <<'PY'
import json, sys
gap_file, story_id = sys.argv[1], sys.argv[2]
reminder = (
    f"Reminder: this question is being asked mid-implementation of story {story_id}. "
    f"Once the user answers, record what was found / asked / decided / done in "
    f"{gap_file} (create it if missing), following the "
    f"'# Story {story_id} - Gaps & Backend Issues' + per-issue "
    f"Found/Asked/Decided/Done format already used in .squad/gaps/ "
    f"(see 31-478.md or 27-474.md). Do this even if the gap gets resolved "
    f"immediately — the record is for whoever revisits this story later, not "
    f"just for open issues."
)
print(json.dumps({
    "hookSpecificOutput": {
        "hookEventName": "PostToolUse",
        "additionalContext": reminder,
    }
}))
PY

log "REMINDED session=${SESSION_ID} gap_file=${GAP_FILE}"
exit 0
