# Laravel Wirecup

Render Wirecup `.cup` files inside a Laravel application at `/wirecup`.

The package auto-registers a small Wirecup browser in your Laravel app, scans a configured directory for `.cup` files, and renders them with a native PHP renderer inside the package.

The package follows the current Wirecup spec from the main `wirecup` project, including route-style link targets such as `b Open|dashboard` and `n Home|/home`.

## Features

- Auto-discovered Laravel package service provider
- `/wirecup` browser page with preview iframe
- `/wirecup/render/{path}` endpoint for direct preview URLs
- Relative Wirecup navigation rewritten to stay inside Laravel routes
- Configurable route prefix, middleware, and source directory
- Testbench test coverage for route registration and rendering

## Requirements

- PHP 8.2+
- Laravel 11, 12, or 13

## Installation

Install the package via Composer:

```bash
composer require ruibeard/laravel-wirecup
```

That is enough for the package to work.

Create a file such as `resources/wirecup/home.cup`, then visit `/wirecup`.

If you want to customize the default route, middleware, or source directory, publish the config file:

```bash
php artisan vendor:publish --tag=wirecup-config
```

## Quick Start

Create `resources/wirecup/home.cup`:

```text
n Home|home Dashboard|admin/dashboard
h Welcome Home
t This page is rendered by the Laravel Wirecup package.
b Open dashboard|admin/dashboard
```

Create `resources/wirecup/admin/dashboard.cup`:

```text
h Dashboard
t This confirms nested .cup files work.
g Name  Status  Action
  Alice  v Draft  b Back|home
  Bob  pending  b Back|home
```

Then open:

```text
http://your-app.test/wirecup
```

## What You Get

- the `/wirecup` page lists all `.cup` files found under the configured root
- selecting a file shows it in an iframe preview
- route-style Wirecup targets like `dashboard`, `/dashboard`, and `admin/dashboard.cup` are rewritten to package preview routes when the target file exists
- nested files like `admin/dashboard.cup` are supported

## Wirecup Link Targets

The upstream Wirecup DSL uses route-like targets instead of `.html` output paths.

Examples:

```text
b Save|settings
n Login|login Dashboard|/dashboard Docs|https://example.com/docs
```

Target behavior in this package matches the current Wirecup spec:

- `http*` stays unchanged
- `/route` resolves from the Wirecup root and is rewritten to the matching package preview route when a `.cup` file exists
- `target.cup` resolves to that `.cup` file and is rewritten to the matching package preview route when it exists
- `target` is treated like a route-style Wirecup target and resolves to `target.cup` from the Wirecup root
- links without a target remain non-navigating

## Configuration

Published config file: `config/wirecup.php`

```php
return [
    'enabled' => env('WIRECUP_ENABLED', true),
    'uri' => env('WIRECUP_URI', 'wirecup'),
    'title' => env('WIRECUP_TITLE', 'Wirecup'),
    'middleware' => ['web'],
    'root' => resource_path('wirecup'),
    'default_file' => env('WIRECUP_DEFAULT_FILE'),
];
```

Example customizations:

```php
return [
    'uri' => 'mockups',
    'middleware' => ['web', 'auth'],
    'root' => resource_path('mockups'),
    'default_file' => 'home.cup',
];
```

## Zero-Config Behavior

You do not need to publish anything for the package to work.

After `composer require`, Laravel package discovery loads the service provider automatically, and the package works with its built-in defaults.

Only publish config if you want to change:

- route prefix
- middleware
- source directory
- default file

## Local Package Development

For local development with this workspace, add a path repository in the consuming Laravel app:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-wirecup"
        }
    ]
}
```

Then require it:

```bash
composer require ruibeard/laravel-wirecup:@dev
```

## How It Works

The package route layer and renderer are both native to the Laravel package.

That is a deliberate choice:

- Laravel users do not need Python on the host machine
- installation is just Composer plus Laravel package discovery
- the renderer stays aligned with the current Wirecup DSL without requiring the upstream preview server

## Testing

```bash
composer test
```

For browser-level checks during development, use a normal Laravel app that requires this package through a local path repository.
