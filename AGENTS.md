# Blogr Docs AGENTS.md

## ⚠️ Issue creation — MANDATORY

**Every user request for a bug fix or new feature MUST trigger a GitHub
issue before any code is written or proposed.** This ensures traceability.

- User says "there is a bug" → create issue with `--label bug`
- User says "I need a feature" → create issue with `--label feature`
- The issue is created via `gh issue create` immediately upon understanding the need
- The issue MUST be closed when the work is merged into `main` — the PR description MUST include `Closes #<issue_number>` to auto-close on merge
- Skipping this is a process error

## ⚠️ Commit policy — ZERO TOLERANCE

**NEVER commit, amend, tag, or push unless the user explicitly loads the
`release-manager` skill and requests a release.** All commits must go
through the `release-manager` workflow. Violating this rule is a process error.

## ⚠️ TDD requirement — ZERO TOLERANCE

**Every bug fix and every feature addition MUST be driven by tests written
first (TDD).** Before writing implementation code, write the test that proves
the bug exists or the feature works. Run the test to confirm it fails (RED),
implement the fix/feature (GREEN), then run the test again to confirm it passes.

### Naming convention

- **Bug regression tests**: `regression_<issue_number>_<description>`
- **Feature tests**: `feature_<description>`

### RED phase (mandatory before any implementation)

1. Write the test that proves the bug exists or validates the expected feature behavior
2. Run `vendor/bin/pest --filter <test_name>` — confirm it **fails** (RED)
3. This proves the test detects the problem

### GREEN phase

1. Implement the fix or feature
2. Run `vendor/bin/pest --filter <test_name>` — confirm it **passes** (GREEN)
3. **Anti-false-positive gate**: Comment out the new implementation code and re-run the test — it must fail again. If it still passes, the test is a false positive and must be rewritten

### Regression test commitment

A regression test becomes part of the **permanent test suite**. It runs on every
`vendor/bin/pest --parallel` execution and on CI. Any future re-introduction of
the bug will be caught immediately.

## Project

FilamentPHP v4 plugin package (`happytodev/blogr-docs`) — a hierarchical documentation
system for Blogr, with learning paths, media embeds, AI translation, and PDF export.

## Resources

| File | Content |
|------|---------|
| [README.md](README.md) | Installation, prerequisites, basic commands |
| [docs/LIVEWIRE-419-BUG.md](docs/LIVEWIRE-419-BUG.md) | Root cause analysis of the 419 Livewire bug |

## Stack

- PHP 8.3+, Laravel 12.x, FilamentPHP v4, Pest PHP 4.0
- Testbench 10.x, in-memory SQLite
- Spatie Package Tools, Barryvdh DomPDF, League CommonMark
- Spatie Shiki PHP (for syntax highlighting via Node.js)

## Commands

```bash
vendor/bin/pest --parallel        # Run all tests
vendor/bin/pest --parallel --ci   # CI mode
vendor/bin/pest --filter "test name"
php -l src/SomeFile.php           # Syntax check before commit
```

## Testing quirks

- `Pest.php` applies `Happytodev\BlogrDocs\Tests\TestCase` to all Feature and Unit tests
- `TestCase` registers `Barryvdh\DomPDF\ServiceProvider` and `Livewire\LivewireServiceProvider`
- `TestCase::defineEnvironment()` sets `livewire.class_namespace = App\Livewire` to match production
- PDF tests catch DomPDF exceptions and mark skipped if the library is unavailable
- Architecture tests forbid `dd()`, `dump()`, `ray()` (via `tests/ArchTest.php`)
- SQLite in-memory database, migrations loaded from `database/migrations/`

## Architecture

- **Models**: `DocArticle` (parent) + `DocArticleTranslation` (translatable content), `DocLearningPath`
- **Routes**: Registered in `BlogrDocsServiceProvider::registerRoutes()` — prefix `docs/` by default
- **Controllers**: `DocController` renders articles, generates PDF, handles locale fallback
- **Helpers**: `DocTreeHelper` builds sidebar tree, nav order, prev/next navigation
- **Filament**: `DocArticleResource`, `DocLearningPathResource`, `DocsSettings` page
- **Service Provider**: `BlogrDocsServiceProvider` (extends `PackageServiceProvider`)
- **Plugin**: `BlogrDocsPlugin` implements `BlogrExtension + FilamentPlugin`

## Filament v4 gotchas

- **`Schema`** (not `Form`): Use `Filament\Schemas\Schema`, `Filament\Schemas\Components\Section`
- **Table actions**: Use `Filament\Actions\Action` (NOT `Filament\Tables\Actions`) for table inline actions
- **Repeater → relationship**: Use `Repeater::make('translations')->relationship('translations')`
- **Re-ordering**: `->reorderable('position')` on table enables drag-and-drop (click button first)
- **Register Livewire aliases**: EVERY Filament page component MUST be registered in `registerLivewireComponents()` in `packageBooted()` — see `docs/LIVEWIRE-419-BUG.md` for why

## Livewire 419 — critical knowledge

The `/livewire/update` route does NOT go through Filament's `SetUpPanel` middleware.
Without explicit `Livewire::component()` registration, the `ComponentRegistry` is empty
on AJAX requests. This causes `ComponentNotFoundException` → `LivewireReleaseTokenMismatchException` → 419.

**Fix**: All Filament page components are registered in `BlogrDocsServiceProvider::registerLivewireComponents()`
(called from `packageBooted()`). Test coverage in `tests/Feature/DocSettingsTest.php`.

## Shiki syntax highlighting

- Uses `spatie/shiki-php` via the `ShikiCodeBlockRenderer` from blogr core
- Requires Node.js on the server
- Install: `npm install shiki` OR `php artisan blogr:install-shiki` (from blogr core v1.28+)
- Fallback: if Shiki fails, code renders as `shiki-fallback` with escaped HTML

## Key conventions

- **Position**: articles are ordered by `position` column. Drag-and-drop in the table, auto-assigned on create
- **Hierarchy**: `parent_id` determines tree structure, visible in the sidebar
- **Translations**: Translation-First pattern — translatable fields in `DocArticleTranslation`
- **Routes**: PDF routes registered BEFORE catch-all `show` routes
- **Converter**: Markdown converter stored as `blogr-docs.converter` singleton

## Release

See `.opencode/skills/release-manager/SKILL.md`.
