# Laravel Wirecup

Install the package, publish the Wirecup skill, then open `/wirecup` to preview `.agents/.cup/*.cup` mockups inside your Laravel app.

## Install

```bash
composer require ruibeard/laravel-wirecup
php artisan wirecup:install
```

This publishes:

- `.agents/skills/wirecup/SKILL.md`
- `.agents/.cup/.gitkeep`

Then ask your LLM to use the local Wirecup skill at `.agents/skills/wirecup/SKILL.md` and write mockups into `.agents/.cup`.

Open:

`http://yourproject.test/wirecup`

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
