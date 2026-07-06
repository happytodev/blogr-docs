# Changelog

## [v1.4.2](https://github.com/happytodev/blogr-docs/compare/v1.4.1...v1.4.2) - 2026-07-06

### 🐛 Bug Fixes

- **TOC not visible**: Change responsive breakpoint from `xl:block` to `lg:block` so the TOC appears at ≥1024px instead of ≥1280px.

## [v1.4.1](https://github.com/happytodev/blogr-docs/compare/v1.4.0...v1.4.1) - 2026-07-06

### 🐛 Bug Fixes

- **TOC rendering on production**: Move `<aside>` wrapper inside `@section('toc')` to fix Blade section evaluation order. The conditional `@if(yieldContent('toc'))` in the layout was evaluated before `@yield('doc-content')` was processed, so the section didn't exist yet on production. Works on local with fresh OPcache but fails on production.

## [v1.4.0](https://github.com/happytodev/blogr-docs/compare/v1.3.1...v1.4.0) - 2026-07-06

### ✨ Features

- **table of contents**: The `Display table of contents` toggle now generates an actual `<ul class="toc-list">` sidebar on the right. Heading IDs are always injected for anchor links regardless of the toggle state.
- **layout**: Sidebar doc font reduced to `text-xs`, TOC uses `text-xs`, sidebar/TOC widths adjusted for better 3-column fit.

### 🐛 Bug Fixes

- **heading permalink**: `extractToc()` and `injectHeadingIds()` now strip the `#` permalink from heading text when generating the TOC and the slug.

### 🧪 Tests

- 3 new tests: TOC shown when enabled, TOC hidden when disabled, heading IDs always present (regression test for issue #4).

## [v1.3.1](https://github.com/happytodev/blogr-docs/compare/v1.3.0...v1.3.1) - 2026-07-06

### 🐛 Bug Fixes

- **table action**: Fix `Class "Filament\Tables\Actions\Action" not found` — use correct namespace `Filament\Actions\Action` for table inline actions.

## [v1.3.0](https://github.com/happytodev/blogr-docs/compare/v1.2.9...v1.3.0) - 2026-07-05

### ✨ Features

- **reorderable table**: Articles can now be reordered via drag-and-drop in the list view. The `position` field is removed from the form — it is managed automatically by drag-and-drop.
- **auto-position**: New articles are automatically assigned the last position (`max(position) + 1`) on creation.
- **quick parent change**: A "Parent" action on each table row lets you change the parent article via a searchable select, without opening the edit page.

## [v1.2.9](https://github.com/happytodev/blogr-docs/compare/v1.2.8...v1.2.9) - 2026-07-05

### 🐛 Bug Fixes

- **syntax highlighting**: Ensure Node.js binary is discoverable on Linux servers by adding `/usr/bin`, `/bin`, `/usr/local/bin` to PATH in `ensureNodeInPath()`. Resolves `shiki-fallback` when Node is installed via apt (at `/usr/bin/node`) but PHP-FPM has a restricted PATH.

## [v1.2.8](https://github.com/happytodev/blogr-docs/compare/v1.2.7...v1.2.8) - 2026-07-05

### ✨ Features

- **syntax highlighting**: Register `ShikiCodeBlockRenderer` in the docs Markdown converter for server-side code syntax highlighting via `spatie/shiki-php`. Language badges, copy buttons, and line numbers are already styled in the blog layout.

## [v1.2.7](https://github.com/happytodev/blogr-docs/compare/v1.2.6...v1.2.7) - 2026-07-05

### 🧪 Tests

- **regression test**: Add `all page components are resolvable from Livewire ComponentRegistry` test validating every Filament page component is registered as a Livewire alias. Simulates production config (`livewire.class_namespace = App\Livewire`). Catches the `ComponentNotFoundException` → `LivewireReleaseTokenMismatchException` → 419 chain automatically on CI.

## [v1.2.6](https://github.com/happytodev/blogr-docs/compare/v1.2.5...v1.2.6) - 2026-07-05

### 🐛 Bug Fixes

- **Livewire 419**: Register all blogr-docs Filament pages as Livewire component aliases in `packageBooted()` — fix timing issue where `app(ComponentRegistry::class)` could return a fresh instance if blogr-docs loaded before Livewire during the `register()` phase.

## [v1.2.4](https://github.com/happytodev/blogr-docs/compare/v1.2.3...v1.2.4) - 2026-07-05

### 🐛 Bug Fixes

- **route collision**: Localized routes (`blogr-docs.pdf`, `blogr-docs.index`, `blogr-docs.show`) now use a `.localized` suffix to avoid name collision with non-localized routes. This fixes `LogicException` when running `php artisan optimize` and prevents potential route cache corruption when locales are enabled.

## [v1.2.3](https://github.com/happytodev/blogr-docs/compare/v1.2.2...v1.2.3) - 2026-07-05

### 🐛 Bug Fixes

- **auto-registration**: Replace `booted()` callback with `afterResolving(PanelRegistry::class, ...)` hook in `packageRegistered()`, matching blogr-artist pattern. This fixes the `RouteNotFoundException` caused by plugins being registered after route compilation.

### ♻️ Refactoring

- **plugin class**: `BlogrDocsPlugin` now implements both `BlogrExtension` and `FilamentPlugin` directly, with disable-check via `blogr_extension_states` table. Anonymous extension class removed.
- **service provider**: Stale `ShikiCodeBlockRenderer` import and `RegistersLinkTypes` trait removed. Extension registration simplified — no longer wrapped in `booted()` callback.

## [v1.2.2](https://github.com/happytodev/blogr-docs/compare/v1.2.1...v1.2.2) - 2026-07-04

### ✨ Features

- Full Filament admin CRUD for docs articles and learning paths
- AI translation support
- PDF export for articles
- Markdown tables and media embeds (YouTube, Spotify, SoundCloud, Deezer, Apple Podcasts)
- Search, drafts, versions, sidebar, breadcrumbs, prev/next navigation
- Docs settings page with tabs (General, Sidebar, TOC, Search, PDF, Embeds)

## [v1.2.1](https://github.com/happytodev/blogr-docs/compare/v1.2.0...v1.2.1) - 2026-07-04

### 🐛 Bug Fixes

- PDF routes registered before catch-all `show` routes to avoid 404
- Config written to package config path

## [v1.2.0](https://github.com/happytodev/blogr-docs/compare/v1.1.1...v1.2.0) - 2026-07-04

### ✨ Features

- Initial public release
