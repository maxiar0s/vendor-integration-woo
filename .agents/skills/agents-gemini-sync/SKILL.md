---
name: agents-gemini-sync
description: Sync `AGENTS.md` and `GEMINI.md` skill registry sections from both repository-local skills (`./.agents/skills`) and global skills (`~/.agents/skills`). Use when creating, renaming, deleting, or updating skills and you need agent docs to reflect current available skills.
---

# Agents/Gemini Sync

Keep `AGENTS.md` and `GEMINI.md` aligned with installed skills.
Run the bundled script to regenerate a `### Skills Registry` section in both files.

## Workflow

1. Confirm repository root contains `AGENTS.md` and `GEMINI.md`.
2. Run the sync script.
3. Review generated diffs.
4. If needed, re-run in dry-run mode to troubleshoot without writing.

## Commands

Run from repository root:

```bash
python .agents/skills/agents-gemini-sync/scripts/sync_docs.py
```

Dry run (no writes):

```bash
python .agents/skills/agents-gemini-sync/scripts/sync_docs.py --dry-run
```

Custom root path:

```bash
python .agents/skills/agents-gemini-sync/scripts/sync_docs.py --repo-root /path/to/repo
```

## Output Contract

- Script updates only the `### Skills Registry` section.
- If section exists, script replaces it.
- If missing, script inserts it before `## Build, Lint, and Test Commands`.
- Skill sources are labeled `repo` or `global`.

## Notes

- This script intentionally does not overwrite manual policy sections.
- Frontmatter parsing is lightweight and supports common `name`, `description`, and optional `metadata` shapes.
- If a `SKILL.md` has no valid frontmatter `name`, it is skipped.

## Bundled Resources

- `scripts/sync_docs.py`: discovers skills and syncs docs.
