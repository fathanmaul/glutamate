<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Glutamate\Columns\IdColumn;
use Glutamate\Columns\IntColumn;
use Glutamate\Columns\StringColumn;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Workbench\Database\Factories\UserFactory;

#[Fillable(['name', 'email', 'age', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function id(): IdColumn
    {
        return IdColumn::make()->as(__FUNCTION__);
    }

    public static function name(): StringColumn
    {
        return StringColumn::make()->maxLength(100)->as(__FUNCTION__);
    }

    public static function email(): StringColumn
    {
        return StringColumn::make()->maxLength(191)->unique()->as(__FUNCTION__);
    }

    public static function age(): IntColumn
    {
        return IntColumn::make()->unsigned()->nullable()->as(__FUNCTION__);
    }
}
