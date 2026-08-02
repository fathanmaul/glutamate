<div align="center">
    <h1>Glutamate</h1>
    <p>A type-safe schema layer for Laravel — defined directly inside Eloquent Models, auto-generated & versioned migrations, auto-updated type-safe PHPDoc docblocks, and typo-safe queries, all on top of Eloquent (not a replacement for it).</p>
</div>

<p align="center">
    <a href="https://packagist.org/packages/lutfisobri/glutamate"><img src="https://img.shields.io/packagist/v/lutfisobri/glutamate.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/lutfisobri/glutamate"><img src="https://img.shields.io/packagist/php-v/lutfisobri/glutamate.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/lutfisobri/glutamate"><img src="https://badge.laravel.cloud/badge/lutfisobri/glutamate?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/lutfisobri/glutamate/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/lutfisobri/glutamate/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/lutfisobri/glutamate"><img src="https://img.shields.io/packagist/dt/lutfisobri/glutamate.svg?style=flat-square" alt="Total Downloads"></a>
</p>

## Why Glutamate

Eloquent has no compile-time check for column names or types — `$user->emial` (typo) silently returns `null` instead of erroring. Glutamate adds a schema definition layer directly onto your Models that:

- **Generates and auto-diffs your migrations** from Model class declarations, instead of hand-writing them
- **Auto-generates PHPDoc `@property` docblocks** on your Model classes for 100% type-safe IDE autocomplete and PHPStan validation
- **Catches typo'd column names and mismatched value types** via static analysis (PHPStan/Larastan), not at runtime
- **Stays fully compatible with Eloquent** — your Models, relations, and queries keep working exactly as before

---

## Installation

You can install the package via Composer:

```bash
composer require lutfisobri/glutamate
```

You can publish the package configuration:

```bash
php artisan vendor:publish --tag="glutamate-config"
```

---

## Usage

### 1. Define Columns inside Eloquent Models

Define the table structure once, directly inside your Eloquent Models as static methods returning `Column` or `ColumnGroup` objects:

```php
use Glutamate\Columns\IdColumn;
use Glutamate\Columns\StringColumn;
use Glutamate\Columns\IntColumn;
use Glutamate\Columns\TimestampsColumn;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public static function id(): IdColumn
    {
        return IdColumn::make()->as(__FUNCTION__);
    }

    public static function title(): StringColumn
    {
        return StringColumn::make()->as(__FUNCTION__)->maxLength(200);
    }

    public static function views(): IntColumn
    {
        return IntColumn::make()->as(__FUNCTION__)->unsigned()->default(0);
    }

    public static function timestamps(): TimestampsColumn
    {
        return TimestampsColumn::make();
    }
}
```

### 2. Run Sync or Push Commands

To generate migrations and instantly update your Model class docblocks with type-safe properties:

```bash
# Generate migration files + update Model docblocks + run artisan migrate
php artisan glutamate:sync

# Just generate migration files and update Model docblocks (without running migrate)
php artisan glutamate:generate

# Prototyping: apply schema changes directly to the database without generating migration files
php artisan glutamate:push
```

After running generate/sync, Glutamate will automatically inject/update the PHPDoc docblocks on your model classes:

```php
/**
 * @property int $id
 * @property string $title
 * @property int $views
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Post extends Model
{
    // ...
}
```

### 3. Query Safely

With the auto-generated docblocks, accessing model properties is fully type-hinted and checked by IDE / PHPStan. For querying, you can pass Column objects directly to query builder methods to avoid typos:

```php
// Post::title() evaluates to "title" at runtime
$posts = Post::where(Post::title(), 'My Post Title')->get();
```

If you use PHPStan with the Glutamate extension, any mismatched query type will be caught at static-analysis time:

```php
// PHPStan Error: Parameter #2 of query method where() expects int, string given.
Post::where(Post::views(), 'not-an-integer')->get();
```

---

## License

Glutamate is open-sourced software licensed under the [MIT license](LICENSE.md).