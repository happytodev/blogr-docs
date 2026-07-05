# Livewire 419 "Page Expired" — Root Cause Analysis

## Symptoms

- HTTP 419 (unknown status) on Livewire POST requests only
- No error in `storage/logs/laravel-*.log`
- No debug page even with `APP_DEBUG=true`
- Works on initial page load, fails on subsequent Livewire updates
- Affects admin panel pages: locale switch in Repeater, form interactions, etc.

## Root Cause Chain

### Layer 1: The middleware gap

The `/livewire/update` route is registered by Livewire with only the `web` middleware group:

```php
Route::post('/livewire/update', $handle)->middleware('web');
```

It does **not** go through Filament's `SetUpPanel` middleware. This middleware is what registers all Filament panel pages as Livewire components in the `ComponentRegistry`.

**Consequence**: On the Livewire update route, the `ComponentRegistry` is empty — no Filament pages are registered.

### Layer 2: The release token verification

When Livewire processes a component update, it verifies the **release token** — a security mechanism that detects deployment-triggered session invalidation:

```php
// Livewire/ReleaseToken.php — verify()
$componentClass = app(ComponentRegistry::class)->getClass($snapshot['memo']['name']);
```

The first thing `verify()` does is **resolve the component class** from its name. This requires the `ComponentRegistry`.

### Layer 3: The wrong class generation

When `getClass()` fails to find the component name in the registry, it falls through to `generateClassFromName()`:

```php
// Livewire/ComponentRegistry.php — generateClassFromName()
$rootNamespace = config('livewire.class_namespace', 'App\\Livewire');

$class = collect(str($name)->explode('.'))
    ->map(fn ($segment) => str($segment)->studly())
    ->join('\\');

return '\\' . $rootNamespace . '\\' . $class;
```

For the component name `happytodev.blogr-docs.filament.resources.pages.create-doc-article`:
- Studly segments → `Happytodev\BlogrDocs\Filament\Resources\Pages\CreateDocArticle`
- Prepended with `App\Livewire` → **`App\Livewire\Happytodev\BlogrDocs\Filament\Resources\Pages\CreateDocArticle`**

This class **does not exist** → `ComponentNotFoundException`.

### Layer 4: HttpException bypasses APP_DEBUG

The exception is caught by `ReleaseToken::verify()` and re-thrown as `LivewireReleaseTokenMismatchException`:

```php
catch (ComponentNotFoundException) {
    throw new LivewireReleaseTokenMismatchException;
}
```

This exception extends Symfony's `HttpException`. **Laravel treats `HttpException` as a controlled HTTP response, regardless of `APP_DEBUG`**:
- No debug error page shown
- No entry written to `storage/logs/laravel-*.log`
- Empty response body with HTTP status 419

This is why all our debug appeared to show PHP responding normally. The response was replaced at the `HttpException` level, before any middleware could log or output it.

## Solution

Register all Filament page components explicitly with Livewire's `ComponentRegistry` in the service provider's **boot phase**, not the register phase.

```php
// BlogrDocsServiceProvider.php

public function packageBooted(): void
{
    $this->registerLivewireComponents();
    // ...
}

protected function registerLivewireComponents(): void
{
    $components = [
        CreateDocArticle::class,
        EditDocArticle::class,
        // ... all page classes
    ];

    foreach ($components as $componentClass) {
        $name = app(ComponentRegistry::class)->getName($componentClass);
        app(ComponentRegistry::class)->component($name, $componentClass);
    }
}
```

### Why `packageBooted()` (boot phase) and not `packageRegistered()` (register phase)

The `ComponentRegistry` is a singleton registered by `LivewireServiceProvider::registerMechanisms()` via `Mechanism::register()`:

```php
// Mechanism.php
function register()
{
    app()->instance(static::class, $this);
}
```

If the plugin's service provider is loaded **before** Livewire's provider (which varies based on Composer autoloader order), `app(ComponentRegistry::class)` in the **register** phase creates a **fresh instance** that is later replaced by Livewire's `register()` call. The aliases are registered on the wrong instance.

In the **boot** phase (`packageBooted()`), all providers have been registered, so `app(ComponentRegistry::class)` returns the **correct** singleton instance.

## Plugin Audit Results

| Plugin | Status | Notes |
|---|---|---|
| **blogr-docs** | ✅ **FIXED v1.2.6** | Registers in `packageBooted()` |
| **blogr-gdpr** | ✅ SAFE | Registers all 6 components via `Livewire::component()` in `boot()` |
| **blogr-artist** | ✅ SAFE | Registers all 4 components via `Livewire::component()` in `packageBooted()` |
| **blogr-core** | ✅ SAFE | Uses standard Filament panel mechanism (no explicit registration needed for panel pages) |
| **blogr-comments** | ⚠️ **VULNERABLE** | Only `CommentSettings` registered manually; `ListComments`, `ViewComment`, `PendingCommentsWidget` are not |

## Prevention

### For package authors

Add a regression test that validates all page components are resolvable from the `ComponentRegistry`, with the production `livewire.class_namespace` config:

```php
test('all page components are resolvable from Livewire ComponentRegistry', function () {
    $pages = [
        CreateDocArticle::class,
        EditDocArticle::class,
        // ... all page classes
    ];

    foreach ($pages as $page) {
        $name = app(ComponentRegistry::class)->getName($page);
        $resolved = app(ComponentRegistry::class)->getClass($name);
        expect($resolved)->toBe($page);
    }
});
```

### For CI/CD

Add a script that checks every Filament page/resource class has a corresponding `Livewire::component()` call, or move all page registrations to `packageBooted()`.

## Detection

To verify if a plugin has the vulnerability, load this route on the production server:

```php
Route::any('/debug-livewire', function () {
    $snapshot = ['memo' => [
        'name' => 'happytodev.vendor.plugin.pages.page-name',
        'release' => 'a-a-a',
    ]];

    try {
        $class = app(ComponentRegistry::class)->getClass($snapshot['memo']['name']);
        $found = $class;
    } catch (\Throwable $e) {
        $found = 'NOT FOUND: ' . $e->getMessage();
    }

    return response()->json([
        'component_found' => $found,
        'config_class_namespace' => config('livewire.class_namespace', 'NOT_SET'),
    ]);
});
```

If `component_found` is `NOT FOUND`, the component is missing from the `ComponentRegistry`.
