# Laravel Wirecup

Install this package, ask your LLM to generate Wirecup mockups using the canonical Wirecup skill at:

`https://github.com/ruibeard/wirecup/blob/main/.agents/skills/wirecup/SKILL.md`

Then open:

`http://yourproject.test/wirecup`

The package will render the generated `.cup` files from `.agents/.cup` inside your Laravel app.

## Install

```bash
composer require ruibeard/laravel-wirecup
```

## Config

If you want a different path, middleware, or root directory:

```bash
php artisan vendor:publish --tag=wirecup-config
```

Published config: `config/wirecup.php`

```php
return [
    'enabled' => env('WIRECUP_ENABLED', true),
    'uri' => env('WIRECUP_URI', 'wirecup'),
    'title' => env('WIRECUP_TITLE', 'Wirecup'),
    'middleware' => ['web'],
    'root' => base_path('.agents/.cup'),
    'default_file' => env('WIRECUP_DEFAULT_FILE'),
];
```
