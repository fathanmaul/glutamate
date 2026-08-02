# Glutamate

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `lutfisobri/glutamate`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Quick Commands

- Full validation: `composer test`
- Formatting check: `composer lint:check`
- Static analysis: `composer analyse`
- Pest tests: `composer test:unit`
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4 and Orchestra Testbench.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.

## What This Package Is

Glutamate is a Laravel package that adds a type-safe schema layer directly on top of Eloquent Models — without replacing Eloquent. It does NOT compete with Doctrine/Laravel Doctrine (which replaces the ORM entirely). Glutamate keeps Eloquent as the runtime query/persistence engine, and adds three things around it:

1. **Model schema definitions** — defined directly in Eloquent Models using a static Column/ColumnGroup API.
2. **Migration generation + auto-versioning** — migrations are generated (and diffed) from Model definitions, not hand-written.
3. **Auto-updated docblocks** — PHPDoc `@property` annotations are automatically inserted and kept in sync on the Model class for IDE autocomplete and PHPStan safety.
4. **Type-safe query helpers** — a thin layer that prevents typo'd column names and wrong value types at static-analysis time, while the actual query execution still goes through Eloquent (or raw queries).

## Hard Rules — Do Not Violate

These were decided deliberately after evaluating and rejecting alternatives. Do not "improve" around them without flagging it first.

### 1. Direct Eloquent Model Integration

`Entity` base class has been removed. Schema definitions reside directly in native Eloquent Models (e.g. `App\Models\Post extends Model`). Do not add static query-execution methods on columns or groups; query execution belongs entirely to the Eloquent Model or to type-safe wrappers.

```php
// RIGHT — go through the real Eloquent model
User::where(User::email(), 'x@example.com')->first();
```

### 2. Column Name Naming Convention

Columns are defined as static methods returning a `Column` or `ColumnGroup` instance:

```php
class User extends Model
{
    public static function emailAddress(): StringColumn
    {
        return StringColumn::make()->as(__FUNCTION__)->maxLength(191)->unique(); // resolved: email_address -> email_address
    }

    public static function userId2fa(): StringColumn
    {
        return StringColumn::make('user_id_2fa'); // explicit name
    }
}
```

Every column should define its name explicitly using `.as(__FUNCTION__)` or via parameter to `make('name')` to ensure that it has a resolved name at runtime when used in Eloquent queries. Do not use dynamic backtrace name detection on runtime due to runtime overhead.

### 3. Separation of Column and ColumnGroup (Composite Pattern)

Glutamate distinguishes between Single Columns and Composite Column Groups:
- **`SchemaElement`** is the base interface that defines `getColumns(): array`.
- **`Column`** (abstract class) extends `SchemaElement` and represents a single database column. It returns `[$this->getName() => $this]` from `getColumns()`.
- **`ColumnGroup`** (abstract class) extends `SchemaElement` and represents composite columns (like `TimestampsColumn`). `TimestampsColumn` returns both `created_at` and `updated_at` datetime columns from `getColumns()`.
- **`SchemaCompiler`** flattens all `SchemaElement` objects returned by the static methods of a Model.

## Core Components

1. **`SchemaElement`**, **`Column`**, and **`ColumnGroup`** — classes and interfaces for defining schema elements.
2. **`DocblockGenerator`** — reads model columns and automatically writes or updates `@property` tags above class declarations (taking care to place them above any PHP 8 attributes).
3. **`SchemaCompiler`** — reflection-based reader: given a Model class, invokes its static schema methods and returns the resolved flat `Column` list.
4. **Migration generator** — turns a compiled column list into a full migration file.
5. **Snapshot + diff engine** — snapshots each Model's structure, diffs against the previous snapshot, and generates a delta migration.
6. **Commands** (`glutamate:generate`, `glutamate:push`, `glutamate:sync`) — interfaces to generate migrations, push schema changes directly, and orchestrate Laravel migrations.

## Non-Goals

- Do not replace Eloquent Models, relations, observers, scopes, or events.
- Do not aim for runtime-only type enforcement — Larastan/PHPStan integration is central to the value proposition, not optional tooling.
- Do not reimplement Eloquent's query engine; always delegate to it.