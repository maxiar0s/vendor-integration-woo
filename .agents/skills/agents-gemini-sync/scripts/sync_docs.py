#!/usr/bin/env python3
"""Sync AGENTS.md and GEMINI.md skill registries.

Scans skills from both:
- <repo>/.agents/skills
- <home>/.agents/skills

Updates a generated section in both AGENTS.md and GEMINI.md.
"""

from __future__ import annotations

import argparse
import os
import re
from pathlib import Path
from typing import Dict, List, Optional


REGISTRY_HEADING = "### Skills Registry"


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Sync AGENTS.md and GEMINI.md skill registry sections"
    )
    parser.add_argument(
        "--repo-root",
        default=os.getcwd(),
        help="Repository root path (defaults to current directory)",
    )
    parser.add_argument(
        "--dry-run", action="store_true", help="Print changes without writing files"
    )
    return parser.parse_args()


def extract_frontmatter(text: str) -> str:
    if not text.startswith("---\n"):
        return ""

    end = text.find("\n---\n", 4)
    if end == -1:
        return ""

    return text[4:end]


def _strip_quotes(value: str) -> str:
    value = value.strip()
    if len(value) >= 2 and (
        (value[0] == '"' and value[-1] == '"') or (value[0] == "'" and value[-1] == "'")
    ):
        return value[1:-1].strip()
    return value


def parse_frontmatter(frontmatter: str) -> Dict[str, object]:
    result: Dict[str, object] = {
        "name": "",
        "description": "",
        "metadata": {"scope": [], "auto_invoke": []},
    }

    lines = frontmatter.splitlines()
    i = 0

    while i < len(lines):
        line = lines[i]

        top_match = re.match(r"^([a-zA-Z_][a-zA-Z0-9_-]*):\s*(.*)$", line)
        if not top_match:
            i += 1
            continue

        key = top_match.group(1)
        raw = top_match.group(2).strip()

        if key == "name":
            result["name"] = _strip_quotes(raw)
            i += 1
            continue

        if key == "description":
            if raw in ("|", ">"):
                i += 1
                chunks: List[str] = []
                while i < len(lines) and (
                    lines[i].startswith("  ") or lines[i].strip() == ""
                ):
                    chunks.append(lines[i].strip())
                    i += 1
                result["description"] = " ".join([c for c in chunks if c]).strip()
                continue

            result["description"] = _strip_quotes(raw)
            i += 1
            continue

        if key == "metadata":
            i += 1
            meta: Dict[str, List[str]] = {"scope": [], "auto_invoke": []}
            while i < len(lines):
                meta_line = lines[i]
                if not meta_line.startswith("  "):
                    break

                m = re.match(r"^\s{2}([a-zA-Z_][a-zA-Z0-9_-]*):\s*(.*)$", meta_line)
                if not m:
                    i += 1
                    continue

                meta_key = m.group(1)
                meta_raw = m.group(2).strip()

                if meta_key not in ("scope", "auto_invoke"):
                    i += 1
                    continue

                if meta_raw.startswith("[") and meta_raw.endswith("]"):
                    inner = meta_raw[1:-1].strip()
                    if inner:
                        meta[meta_key] = [
                            _strip_quotes(p.strip())
                            for p in inner.split(",")
                            if p.strip()
                        ]
                    i += 1
                    continue

                if meta_raw:
                    meta[meta_key] = [_strip_quotes(meta_raw)]
                    i += 1
                    continue

                i += 1
                values: List[str] = []
                while i < len(lines):
                    item_line = lines[i]
                    if not item_line.startswith("    "):
                        break

                    item_match = re.match(r"^\s*[-]\s*(.*)$", item_line)
                    if item_match:
                        value = _strip_quotes(item_match.group(1).strip())
                        if value:
                            values.append(value)
                    i += 1

                meta[meta_key] = values

            result["metadata"] = meta
            continue

        i += 1

    return result


def read_skill(skill_md: Path, source: str) -> Optional[Dict[str, object]]:
    try:
        text = skill_md.read_text(encoding="utf-8")
    except UnicodeDecodeError:
        text = skill_md.read_text(encoding="utf-8", errors="replace")

    frontmatter = extract_frontmatter(text)
    if not frontmatter:
        return None

    data = parse_frontmatter(frontmatter)
    name = str(data.get("name") or "").strip()
    if not name:
        return None

    description = str(data.get("description") or "").strip().replace("\n", " ")
    metadata_obj = data.get("metadata", {})
    metadata: Dict[str, List[str]] = (
        metadata_obj
        if isinstance(metadata_obj, dict)
        else {"scope": [], "auto_invoke": []}
    )
    scope = list(metadata.get("scope", []))
    auto_invoke = list(metadata.get("auto_invoke", []))

    return {
        "name": name,
        "description": description,
        "source": source,
        "scope": scope,
        "auto_invoke": auto_invoke,
        "path": str(skill_md),
    }


def collect_skills(repo_root: Path) -> List[Dict[str, object]]:
    repo_skills_dir = repo_root / ".agents" / "skills"
    global_skills_dir = Path.home() / ".agents" / "skills"

    skills: List[Dict[str, object]] = []

    if repo_skills_dir.exists():
        for skill_md in sorted(repo_skills_dir.glob("*/SKILL.md")):
            parsed = read_skill(skill_md, "repo")
            if parsed:
                skills.append(parsed)

    if global_skills_dir.exists():
        for skill_md in sorted(global_skills_dir.glob("*/SKILL.md")):
            parsed = read_skill(skill_md, "global")
            if parsed:
                skills.append(parsed)

    skills.sort(key=lambda s: (str(s["name"]).lower(), str(s["source"]).lower()))
    return skills


def build_registry_block(skills: List[Dict[str, object]]) -> str:
    lines = [
        REGISTRY_HEADING,
        "",
        "Auto-generated from `./.agents/skills` (repo) and `~/.agents/skills` (global).",
        "",
        "| Skill | Source | Description |",
        "|-------|--------|-------------|",
    ]

    for skill in skills:
        name = str(skill["name"])
        source = str(skill["source"])
        desc = str(skill.get("description") or "").strip()
        if not desc:
            desc = "-"
        desc = desc.replace("|", "\\|")
        lines.append(f"| `{name}` | {source} | {desc} |")

    if not skills:
        lines.append("| - | - | No skills discovered |")

    return "\n".join(lines) + "\n"


def replace_or_insert_registry(doc_text: str, registry_block: str) -> str:
    pattern = re.compile(r"(?ms)^### Skills Registry\n.*?(?=^### |^## |\Z)")
    if pattern.search(doc_text):
        return pattern.sub(registry_block.rstrip("\n"), doc_text, count=1)

    insert_match = re.search(r"(?m)^## Build, Lint, and Test Commands\n", doc_text)
    if insert_match:
        idx = insert_match.start()
        prefix = doc_text[:idx]
        suffix = doc_text[idx:]
        if not prefix.endswith("\n\n"):
            prefix = prefix.rstrip("\n") + "\n\n"
        return prefix + registry_block + "\n" + suffix

    if not doc_text.endswith("\n"):
        doc_text += "\n"
    return doc_text + "\n" + registry_block


def sync_doc(path: Path, registry_block: str, dry_run: bool) -> bool:
    if not path.exists():
        return False

    original = path.read_text(encoding="utf-8")
    updated = replace_or_insert_registry(original, registry_block)
    changed = updated != original

    if changed and not dry_run:
        path.write_text(updated, encoding="utf-8")

    return changed


def main() -> None:
    args = parse_args()
    repo_root = Path(args.repo_root).resolve()

    skills = collect_skills(repo_root)
    registry_block = build_registry_block(skills)

    targets = [repo_root / "AGENTS.md", repo_root / "GEMINI.md"]

    changed_files: List[str] = []
    for target in targets:
        changed = sync_doc(target, registry_block, args.dry_run)
        if changed:
            changed_files.append(str(target))

    mode = "DRY RUN" if args.dry_run else "UPDATED"
    print(f"[{mode}] Skills discovered: {len(skills)}")
    for skill in skills:
        print(f"- {skill['name']} ({skill['source']})")

    if changed_files:
        print("Files changed:")
        for f in changed_files:
            print(f"- {f}")
    else:
        print("No file changes required.")


if __name__ == "__main__":
    main()
