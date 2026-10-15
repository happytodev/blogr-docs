# Blogr Docs Settings

The docs settings page is available at `/admin/docs-settings` (or your custom admin path).
Settings are persisted to the `blogr_docs_settings` table (one row per dotted config key:
`pdf.watermark.text`, `toc.max_level`, …) and applied on top of the configuration file when
the application boots. An admin save therefore survives deployments, rollbacks,
`composer install` and `config:clear`, while the committed `config/blogr-docs.php` keeps
playing the role of the defaults.

While the `blogr_docs_settings` table has not been migrated yet, the page falls back to the
historical behaviour (writing the config file).

---

## General

| Field | Config key | Default | Description |
|---|---|---|---|
| Enabled | `enabled` | `true` | Enable or disable the documentation system. When disabled, all doc routes return 404. |
| Prefix | `prefix` | `docs` | URL prefix for all documentation routes (e.g. `/docs`, `/help`, `/manual`). |
| Middleware | `middleware` | `['web']` | Middleware applied to frontend documentation routes. |

---

## Sidebar

Configure the documentation navigation tree in the left sidebar.

| Field | Config key | Default | Description |
|---|---|---|---|
| Collapsible | `sidebar.collapsible` | `true` | Allow collapsing/expanding parent article groups. |
| Max depth | `sidebar.max_depth` | `5` | Maximum nesting level displayed in the sidebar tree. |
| Show icons | `sidebar.show_icons` | `true` | Display hero icons next to article titles in the sidebar. |

---

## Table of Contents (TOC)

Configure the "On this page" sidebar on the right of each article.

| Field | Config key | Default | Description |
|---|---|---|---|
| Enabled | `toc.enabled` | `true` | Enable the table of contents sidebar. Also controls heading ID injection (anchor links). |
| Max level | `toc.max_level` | `3` | Maximum heading level displayed in the TOC (`2` = h2 only, `3` = h2 + h3). |

The TOC is generated from `<h2>` and `<h3>` tags in the article content. Each heading gets an `id` attribute for anchor linking.
The TOC sidebar is visible on screens ≥ 1024px (`lg:block`).

Individual articles can override the global TOC setting via the `Display table of contents` toggle in the article edit form.

---

## Search

Server-side search across article titles, content, excerpts, and slugs.

| Field | Config key | Default | Description |
|---|---|---|---|
| Enabled | `search.enabled` | `true` | Show the search field in the sidebar. |
| Min length | `search.min_length` | `2` | Minimum query length to trigger a search. |
| Max results | `search.max_results` | `20` | Maximum number of search results to return. |

Search queries are submitted via `?q=` query parameter to the docs index page.
Results are rendered from the `search.blade.php` view.

---

## PDF Export

Generate PDF downloads of any documentation article.

| Field | Config key | Default | Description |
|---|---|---|---|
| Enabled | `pdf.enabled` | `false` | Show the "Download PDF" button on article pages. |
| Driver | `pdf.driver` | `dompdf` | PDF rendering engine (currently only `dompdf` supported). |
| Page size | `pdf.page_size` | `A4` | Paper size (A4, Letter, Legal, A3, A5). |
| Orientation | `pdf.orientation` | `portrait` | Page orientation (Portrait / Landscape). |

### PDF Watermark

Add a text or image watermark to PDF exports.

| Field | Config key | Default | Description |
|---|---|---|---|
| Enabled | `pdf.watermark.enabled` | `false` | Activate watermark on PDF exports. |
| Text | `pdf.watermark.text` | `Confidential` | Watermark text. Rendered only if no image is provided. Both text and image can be displayed together. |
| Image | `pdf.watermark.image` | `null` | Upload a watermark image. Stored in `storage/app/public/docs/pdf-watermarks/`. |
| Opacity | `pdf.watermark.opacity` | `0.2` | Watermark opacity (0.1 to 1.0). |
| Position | `pdf.watermark.position` | `center` | Watermark position on the page: `center`, `top-left`, `top-center`, `top-right`, `center-left`, `center-right`, `bottom-left`, `bottom-center`, `bottom-right`. |
| Rotation | `pdf.watermark.rotation` | `-45` | Rotation angle in degrees (-90 to 90). |
| Size | `pdf.watermark.size` | `60` | Font size in px for text watermarks, max dimension in px for image watermarks. |

---

## Embedded Media

Enable or disable embedded media providers in the Markdown converter.
These affect how URLs from supported platforms are rendered in article content.

| Field | Config key | Default |
|---|---|---|
| YouTube | `embeds.youtube` | `true` |
| Vimeo | `embeds.vimeo` | `true` |
| Dailymotion | `embeds.dailymotion` | `true` |
| Spotify | `embeds.spotify` | `true` |
| SoundCloud | `embeds.soundcloud` | `true` |
| Deezer | `embeds.deezer` | `true` |
| Apple Podcasts | `embeds.apple_podcasts` | `true` |

---

## SEO

Default metadata used when articles don't provide their own.

| Field | Config key | Default | Description |
|---|---|---|---|
| Site name | `seo.site_name` | `null` | Override the site name for SEO metadata. Falls back to `env('APP_NAME')`. |
| Default title | `seo.default_title` | `Documentation` | Default `<title>` for the docs index page. |
| Default description | `seo.default_description` | `null` | Default meta description for the docs index page. |

---

## Where the settings live

| | |
|---|---|
| Storage | `blogr_docs_settings` (`key` unique, `value` JSON) |
| Applied | at boot, on top of the configuration file (`SettingsRepository::applyToConfig()`) |
| Never applied | while `php artisan config:cache` / `optimize` builds the compiled configuration — the overrides must not be baked into `bootstrap/cache/config.php` |
| Fallback | the config file, only while the table has not been migrated |
| Untouched keys (e.g. `middleware`, `seo.*`, `reserved_slugs`) | from `config/blogr-docs.php` |

The package ships a default `config/blogr-docs.php`, merged during service provider
registration; `php artisan vendor:publish --tag=blogr-docs-config` publishes your own copy.
A `null` value is never stored: it means "unset", so the file's value stays in force.

To reset to defaults, empty the table:

```bash
php artisan tinker --execute="DB::table('blogr_docs_settings')->delete();"
php artisan config:clear
```

The table comes from the package migrations (`php artisan vendor:publish --tag=blogr-docs-migrations`
then `php artisan migrate`). The uploaded watermark image is a file on the public disk
(`storage/app/public/docs/pdf-watermarks/`), not a setting: it survives a deploy on its own.
