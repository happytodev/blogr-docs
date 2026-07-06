---
name: release-manager
description: Automate blogr-docs releases: version bumping, CHANGELOG updates, tagging, and publishing to GitHub.
---

## When to use

Trigger phrases: "release", "tag a new version", "publish vX.Y.Z", "cut a release", "bump version".

## Workflow

### 0. Pre-flight check — all PRs must be merged

Before any release work, verify that no open PRs target `main`:

```bash
git fetch origin main
OPEN_PRS=$(gh pr list --base main --state open --json number,title --jq '.[] | "\(.number) \(.title)"')
```

If `$OPEN_PRS` is not empty, **abort immediately** and display the list of open PRs.

**Do not proceed** until all open PRs targeting `main` are merged.

---

### 1. Preview changes since the last release

```bash
git log $(git describe --tags --abbrev=0)..HEAD --oneline --no-decorate
```

If no tags exist yet, use: `git log --oneline --no-decorate`

### 2. Determine the new version

- Read current version from `src/Blogr.php` (`const VERSION = '...'`)
- Ask user for bump type: `patch`, `minor`, `major`, or explicit version
- Present the computed version to the user for confirmation

### 3. Organize uncommitted changes into feature-grouped commits

- Run `git status --short` to list changed/new files
- If there are no uncommitted changes, skip this step
- If there are uncommitted changes, group files by feature area:

  | Pattern | Suggested commit |
  |---|---|
  | `src/Filament/Resources/DocArticle*`, `src/Models/DocArticle*` | `feat: article CRUD changes` |
  | `src/BlogrDocsServiceProvider*`, `src/BlogrDocsPlugin*` | `fix: service provider / plugin` |
  | `src/Http/Controllers/DocController*` | `fix: route / controller` |
  | `resources/views/*` | `feat: view / Blade changes` |
  | `tests/*` | `test: add tests` |
  | `src/Blogr.php`, `composer.json` | `chore: bump version` |
  | `docs/*` | `docs: update documentation` |

### 4. Generate and present release notes

- Use the commit log from step 1 to format as markdown with conventional commit categories
- First output the full release notes as a text message (markdown code block)
- Ask for approval with `question` tool

### 5. Run tests (ZERO TOLERANCE)

```bash
vendor/bin/pest --parallel
```

If ANY test fails (even 1), abort immediately.

### 6. Update version files (atomic commit)

- **`src/Blogr.php`**: Edit `const VERSION = '...'`
- **Commit**:
  ```bash
  git add src/Blogr.php
  git commit -m "chore: bump version to v{version}"
  ```

### 7. Update CHANGELOG.md (atomic commit)

- Prepend a new entry at the top following the existing format
- Use the user-approved release notes content from step 4
- **Commit**:
  ```bash
  git add CHANGELOG.md
  git commit -m "docs(changelog): v{version}"
  ```

### 8. Sync with remote before tagging

```bash
git fetch origin main
if [ "$(git rev-parse HEAD)" != "$(git rev-parse origin/main)" ]; then
    echo "Local and remote main diverge. Run 'git pull --rebase origin main' first."
    exit 1
fi
```

Check that the tag does not already exist locally or remotely.

### 9. Push main first, then tag

```bash
git push origin main
git tag v{version}
if [ "$(git rev-parse v{version})" != "$(git rev-parse HEAD)" ]; then
    echo "Tag does not match HEAD. Delete and re-tag."
    git tag -d v{version}
    exit 1
fi
git push origin v{version}
```

### 10. Create GitHub Release

```bash
gh release create v{version} --title "v{version}" --notes "$RELEASE_NOTES"
```

### 11. Confirm

Inform the user the release was published with the URL and commit hash.
