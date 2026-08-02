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

Glutamate is a Laravel package that adds a type-safe schema layer on top of Eloquent — without replacing Eloquent. It does NOT compete with Doctrine/Laravel Doctrine (which replaces the ORM entirely). Glutamate keeps Eloquent as the runtime query/persistence engine, and adds three things around it:

1. **Entity classes** — a single source of truth for table structure, defined with a fluent Column API
2. **Migration generation + auto-versioning** — migrations are generated (and diffed) from Entity definitions, not hand-written
3. **Type-safe query helpers** — a thin layer that prevents typo'd column names and wrong value types at static-analysis time, while the actual query execution still goes through Eloquent (or raw queries)

## The Problem Being Solved

In frameworks like Spring Boot or NestJS/TypeORM, the entity class is read by the compiler/type-checker, so a typo'd column name or wrong type is caught before runtime. Laravel/Eloquent has no equivalent: Eloquent uses `__get`/`__set` magic methods over an internal `$attributes` array, so `$user->emial` (typo) silently returns `null` instead of erroring. There is no compiler to catch this.

Glutamate's goal is to simulate compile-time safety in a dynamically-typed language, using static analysis (PHPStan/Larastan) as the enforcement mechanism, and code generation to keep migrations/entities in sync.

## Hard Rules — Do Not Violate

These were decided deliberately after evaluating and rejecting alternatives. Do not "improve" around them without flagging it first.

### 1. `Entity` must never extend `Illuminate\Database\Eloquent\Model`

Tested and rejected: if `Entity` extended `Model` and declared native typed properties (`public string $email`), those properties shadow Eloquent's magic accessors (`__get`/`__set`). Effects: reading `$user->email` stops going through `$attributes`, mass assignment breaks, dirty tracking breaks, `save()` breaks.

`Entity` is a standalone base class, fully independent of Eloquent. Real Eloquent Models (`App\Models\User extends Model`) still exist normally in the consuming app and remain the actual query/persistence layer. Entity is metadata + a hydration target — it never executes queries itself.

**Corollary: `Entity` must never gain a `::where()`, `::find()`, `::query()`, or any other query-executing static method.** Query execution belongs to real Eloquent Models or to the `Query::for(SomeEntity::class)` wrapper (not yet built). If a change starts adding query behavior directly onto `Entity`, stop and flag it — that's the exact anti-pattern rule #1 exists to prevent.

```php
// WRONG — Entity is not a query builder
UserEntity::where(UserEntity::email(), 'x@example.com')->first();

// RIGHT — go through the real Eloquent model, casting the Column to string
User::where((string) UserEntity::email(), 'x@example.com')->first();

// RIGHT — once built, go through the type-safe wrapper
Query::for(UserEntity::class)->where(UserEntity::email(), 'x@example.com')->first();
```

### 2. Column name detection: auto-detect from the immediate caller, with explicit override

Entity columns are defined as static methods returning `Column` instances:

```php
class UserEntity extends Entity
{
    public static function emailAddress(): StringColumn
    {
        return StringColumn::make()->maxLength(191)->unique(); // auto: emailAddress -> email_address
    }

    public static function userId2fa(): StringColumn
    {
        return StringColumn::make('user_id_2fa'); // explicit override, ambiguous name
    }
}
```

`Column::make(?string $name = null)`:
- if `$name` is given, it is used as-is (explicit DB column name — required for ambiguous cases like `userId2fa`)
- if `$name` is `null`, the column name is detected from the immediate caller using a **depth-limited** backtrace: `debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)`, reading only the frame that called `make()`, then converting it with `Str::snake()`

Do NOT use an unbounded `debug_backtrace()` (no depth limit, no `IGNORE_ARGS`) — that was rejected earlier in this project's design for being slow and unreliable across wrapped call chains. The depth-limited, single-frame version above only trusts the *immediate* caller of `make()`, which matches the established convention of calling `Column::make()` directly inline inside the Entity's static column method. If `make()` is ever called through an intermediate helper/trait instead of directly, caller detection will point at the wrong method — in that case the explicit `columnName()`/`make('name')` override must be used. Treat `columnName()` as a required escape hatch, not an edge-case-only feature.

### 3. Column name conversion (camelCase → snake_case) lives in exactly one place

`Column::make()`'s caller-detection path and `SchemaCompiler` (which reads method names via `ReflectionClass` when compiling migrations) both need to agree on how a method name maps to a database column name. Both must call the same shared `Str::snake()`-based helper — do not let them carry independent conversion logic that can drift out of sync.

Note `SchemaCompiler` never needs backtrace at all: it already has the method name for free via `ReflectionMethod::getName()` before it even invokes the method. Its only job regarding names is to prefer the `Column`'s own resolved `getName()` (already set at invocation time by `make()`) over re-deriving anything itself — it should not duplicate the conversion, just trust what `Column` already resolved.

### 4. `Column` implements `Stringable`

`Column::__toString()` returns the resolved column name, so a `Column` instance can be passed anywhere Laravel expects a column-name string (e.g. `User::where((string) UserEntity::email(), ...)`, or unmarked, wherever PHP auto-casts). This is intentional and should be preserved — it's what lets the type-safe layer interoperate with plain Eloquent without every call site needing manual `->name()` calls.

## Core Components (status: mostly not built yet — check `src/` before assuming anything below exists)

1. **`Entity`** (abstract) — base class for schema definitions. Static factory methods per column, name inferred from method name (see rules above).
2. **`Column`** (abstract) + subclasses (`StringColumn`, `IntColumn`, planned: `BoolColumn`, `EnumColumn`, `DecimalColumn`, `DateTimeColumn`, `ForeignIdColumn`) — fluent modifiers (`nullable()`, `default()`, `unique()`, `index()`, `columnName()`), plus `toBlueprint(Blueprint $table, string $name)` for migration generation and `phpType()` for docblocks/casts.
3. **`SchemaCompiler`** (not built) — reflection-based reader: given an Entity class, invoke its static column methods and return the resolved `Column` list keyed by resolved column name.
4. **Migration generator** (not built) — turns a compiled column list into a full migration file.
5. **Snapshot + diff engine / `glutamate:sync` command** (placeholder command exists, no logic) — snapshots each Entity's structure, diffs against the previous snapshot, generates a delta migration (add/change/remove columns only), inspired by Prisma Migrate / Laravel Doctrine's diff command.
6. **`Query::for(EntityClass)`** (not built) — type-safe wrapper accepting `Column` instances in `where()`/`orderBy()`/etc., delegating to real Eloquent underneath.
7. **Hydration helpers** (not built) — `SomeEntity::fromEloquent($collection)` / `SomeEntity::fromRaw($rows)`, mapping query results onto typed Entity instances via reflection.

## Non-Goals

- Do not replace Eloquent Models, relations, observers, scopes, or events.
- Do not aim for runtime-only type enforcement — Larastan/PHPStan integration is central to the value proposition, not optional tooling.
- Do not reimplement Eloquent's query engine; always delegate to it.

## Prior Art (context, not to copy)

- `lepikhinb/laravel-fluent` — closest existing package; typed properties auto-cast on a real Eloquent Model (extends Model directly, unlike Glutamate). No migration generation, no diff/versioning, no type-safe query layer.
- Laravel Doctrine — has a migration-diff command similar in spirit to `glutamate:sync`, but replaces Eloquent with Doctrine entirely. Glutamate deliberately keeps Eloquent.
- `barryvdh/laravel-ide-helper` — generates docblocks only, no migrations, no query safety.

No existing package combines: a standalone Entity/Column definition layer, auto-generated + auto-diffed migrations from that layer, and a type-safe query layer that still runs on real Eloquent. That combination is Glutamate's specific niche — preserve it.