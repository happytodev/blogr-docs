# Blogr Docs

Documentation system plugin for [Blogr](https://github.com/happytodev/blogr).

## Features

- **Hierarchical documentation tree** — nested sections and pages with parent-child relationships
- **Learning paths** — curated, ordered sequences of articles
- **Media embeds** — automatically convert YouTube, Vimeo, Dailymotion, Spotify, SoundCloud, Deezer, and Apple Podcast URLs to responsive embeds
- **Multi-locale** — full translation support following Blogr's Translation-First pattern
- **Sidebar navigation** — auto-generated tree navigation
- **Breadcrumbs** — auto-generated from hierarchy
- **Previous/Next navigation** — between sibling articles
- **Table of contents** — auto-generated from markdown headings
- **Markdown content** — with code highlighting support
- **SEO** — meta titles, descriptions, keywords per translation
- **Draft/version system** — save drafts and track version history
- **PDF export** — download articles as PDF
- **Search** — full-text search across all content
- **Admin UI** — Filament PHP resources for managing the documentation tree

## Installation

```bash
composer require happytodev/blogr-docs

php artisan vendor:publish --tag=blogr-docs-migrations
php artisan migrate
```

Add the plugin to your Filament panel:

```php
use Happytodev\BlogrDocs\BlogrDocsPlugin;

$panel->plugin(BlogrDocsPlugin::make());
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=blogr-docs-config
```

### Config options

```php
// config/blogr-docs.php

'enabled' => true,         // Enable/disable the docs system
'prefix' => 'docs',        // URL prefix for documentation routes

'sidebar' => [
    'collapsible' => true,
    'show_icons' => true,
],

'toc' => [
    'enabled' => true,
    'max_level' => 3,
],

'search' => [
    'enabled' => true,
    'min_length' => 2,
    'max_results' => 20,
],

'pdf' => [
    'enabled' => false,    // Requires dompdf or Browsershot
],

'embeds' => [
    'youtube' => true,
    'vimeo' => true,
    'dailymotion' => true,
    'spotify' => true,
    'soundcloud' => true,
    'deezer' => true,
    'apple_podcasts' => true,
],
```

## Usage

### Creating documentation

1. Access your Filament admin panel
2. Navigate to the "Docs" section
3. Create root-level sections (e.g., "Systems", "Development")
4. Add child articles with markdown content
5. Publish when ready

### Media embeds

Simply paste a supported URL on its own line in your markdown content:

```markdown
Check out this video:

https://www.youtube.com/watch?v=dQw4w9WgXcQ

Listen to this episode:

https://open.spotify.com/episode/7GxUhbCgR
```

## Testing

```bash
composer test
```
