# Changelog

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
