# Contributing

Please open an issue or pull request with a clear description of the problem or improvement.

## Local Setup

```bash
composer install
composer test
```

For manual package development, use a normal Laravel app with a local path repository pointing at this package.

```bash
composer require ruibeard/laravel-wirecup:@dev
```

Then create `resources/wirecup/*.cup` files in that app and open `/wirecup`.

## Guidelines

- Keep the package zero-config by default
- Prefer small changes over framework-heavy abstractions
- Make sure README examples stay aligned with actual behavior
- Add or update tests for behavior changes
