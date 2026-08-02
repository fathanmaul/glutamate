<div align="center">
    <h1>Glutamate</h1>
    <p>A type-safe schema layer for Laravel — Entity definitions, auto-generated & versioned migrations, and typo-safe queries, all on top of Eloquent (not a replacement for it).</p>
</div>

<p align="center">
    <a href="https://packagist.org/packages/lutfisobri/glutamate"><img src="https://img.shields.io/packagist/v/lutfisobri/glutamate.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/lutfisobri/glutamate"><img src="https://img.shields.io/packagist/php-v/lutfisobri/glutamate.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/lutfisobri/glutamate"><img src="https://badge.laravel.cloud/badge/lutfisobri/glutamate?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/lutfisobri/glutamate/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/lutfisobri/glutamate/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/lutfisobri/glutamate"><img src="https://img.shields.io/packagist/dt/lutfisobri/glutamate.svg?style=flat-square" alt="Total Downloads"></a>
</p>

> **Status:** early development. The API below reflects the intended design and may not be fully implemented yet — check the [changelog](CHANGELOG.md) for what's actually shipped.

## Why Glutamate

Eloquent has no compile-time check for column names or types — `$user->emial` (typo) silently returns `null` instead of erroring. Glutamate adds a schema definition layer that:

- generates and auto-diffs your migrations from a single Entity definition, instead of hand-writing them
- catches typo'd column names and mismatched value types via static analysis (PHPStan/Larastan), not at runtime
- stays fully compatible with Eloquent — your Models, relations, and queries keep working exactly as before

## Installation

You can install the package via Composer:

```bash
composer require lutfisobri/glutamate
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="glutamate"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="glutamate-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="glutamate-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="glutamate-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="glutamate-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="glutamate-assets"
```

## Usage

Define a table's structure once, as an Entity:

```php
use Glutamate\Entity;
use Glutamate\Columns\StringColumn;
use Glutamate\Columns\IntColumn;

class UserEntity extends Entity
{
    public static function emailAddress(): StringColumn
    {
        // column name auto-detected from the method name: emailAddress -> email_address
        return StringColumn::make()->maxLength(191)->unique();
    }

    public static function age(): IntColumn
    {
        return IntColumn::make()->unsigned()->nullable();
    }

    public static function userId2fa(): StringColumn
    {
        // ambiguous auto-conversion -> pass the column name explicitly
        return StringColumn::make('user_id_2fa');
    }
}
```

Generate and keep migrations in sync with your Entity definitions:

```bash
php artisan glutamate:sync
```

Query safely — column references are checked by static analysis instead of relying on raw strings, while execution still goes through Eloquent:

```php
use App\Models\User;

User::where((string) UserEntity::emailAddress(), 'lutfi@example.com')->first();
```

> A dedicated `Query::for(UserEntity::class)` wrapper and Larastan rule set are planned — see [CHANGELOG](CHANGELOG.md) / issues for progress.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Glutamate! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Lutfi Sobri](https://github.com/lutfisobri)
- [All Contributors](../../contributors)

## License

Glutamate is open-sourced software licensed under the [MIT license](LICENSE.md).