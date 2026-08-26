#!/usr/bin/env python3
"""
يقرأ Support_CRM_Backend_First_Backlog.csv ويطلع intake.md لأول ستوري لسه ملهاش ملف intake.
الاستخدام: python3 scripts/next_story.py
"""
import csv
import re
import sys
from pathlib import Path

BACKLOG = Path("Support_CRM_Backend_First_Backlog.csv")
STORIES_DIR = Path(".squad-lite/stories")


def slugify(title: str) -> str:
    m = re.match(r"([A-Z]{2}-\d+)", title)
    story_id = m.group(1) if m else "STORY"
    slug = re.sub(r"[^a-z0-9]+", "-", title.lower()).strip("-")[:60]
    return story_id, slug


def strip_html(text: str) -> str:
    return re.sub(r"<[^>]+>", "\n", text).strip()


def main():
    if not BACKLOG.exists():
        print(f"مفيش {BACKLOG} في الجذر.", file=sys.stderr)
        sys.exit(1)

    STORIES_DIR.mkdir(parents=True, exist_ok=True)
    done_ids = {p.stem.split("-")[0] for p in STORIES_DIR.glob("*.md")}

    with BACKLOG.open(encoding="utf-8-sig") as f:
        rows = list(csv.DictReader(f))

    for row in rows:
        story_id, slug = slugify(row["Title"])
        if story_id in done_ids:
            continue

        out_path = STORIES_DIR / f"{story_id}-{slug}.md"
        content = f"""# {row['Title']}

## النوع
{row['Work Item Type']}

## الوصف
{strip_html(row['Description'])}

## معايير القبول
{strip_html(row['Acceptance Criteria'])}

## Tags
{row['Tags']}

---
## سياق المشروع (يُقرأ إلزاميًا قبل التخطيط)
- اقرأ `CLAUDE.md` و `docs/contracts/conventions-digest.md` كاملين.
- لو فيه endpoint مرتبط، دوّر عليه في `docs/contracts/api-contract.md` بـ grep بدل قراءة الملف كامل.
- طبّق الـ layering: Action / FormRequest / Policy / Resource داخل الـ Domain المناسب.
"""
        out_path.write_text(content, encoding="utf-8")
        print(f"✅ اتكتب: {out_path}")
        print("افتح الملف، راجعه (دقيقتين كفاية)، وبعدين ابدأ جلسة Claude Code جديدة وحطه كسياق.")
        return

    print("كل الستوريز في الباكلوج اتعملها intake بالفعل.")


if __name__ == "__main__":
    main()
