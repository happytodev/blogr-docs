---
name: git-changelog-workflow
description: >-
  Analyzes Git changes, proposes CHANGELOG.md entries (user validation required),
  creates atomic Conventional Commits, places work on a type-named branch
  (feat, fix, docs…), and proposes a GitHub Pull Request.
compatibility: >-
  OpenCode agent with git and shell access. Remote GitHub (origin).
metadata:
  author: happytodev
  version: "1.0"
---

# Git, CHANGELOG and Pull Request Workflow

Orchestrates end-of-work on this repository: analysis → branch → CHANGELOG proposal → atomic commits → PR.

**Never** push, commit, or open a PR without explicit user validation at the steps outlined below.

## When to use

- The user asks to commit, prepare a branch, update the CHANGELOG, or create a PR
- A code task is complete and needs to be delivered cleanly

## Workflow

### 1. Understand the uncommitted changes

Run:
```bash
git status --short
git diff --stat
git log --oneline -5          # last 5 commits on current branch
```

### 2. Group changes by type

Classify each changed file using:

| Path pattern | Branch type | Commit type |
|---|---|---|
| `src/Filament/Resources/DocArticle*` | `feat` | `feat(article)` |
| `src/Models/DocArticle*` | `feat` | `feat(model)` |
| `src/BlogrDocsServiceProvider*` | `fix` | `fix(provider)` |
| `src/Http/Controllers/DocController*` | `fix` | `fix(routes)` |
| `resources/views/*` | `feat` | `feat(views)` |
| `tests/*` | `test` | `test` |
| `docs/*`, `AGENTS.md`, `.opencode/skills/*` | `docs` | `docs` |
| `CHANGELOG.md` | — | `docs(changelog)` |
| `src/Blogr.php`, `composer.json` | `chore` | `chore` |

### 3. Create the branch

```bash
git checkout -b {type}/{kebab-description}
```

### 4. Commit in atomic groups

For each group, stage and commit:
```bash
git add <file1> <file2> ...
git commit -m "{type}({scope}): {description}

{optional body}"
```

### 5. Propose and create the PR

```bash
git push -u origin HEAD
gh pr create \
  --base main \
  --title "{type}: {title}" \
  --body "## Summary

{detailed description}

## Changes

- {change 1}
- {change 2}"
```

### 6. Present the PR URL to the user

### 7. After merge, switch back to main

```bash
git checkout main
git pull origin main
```
