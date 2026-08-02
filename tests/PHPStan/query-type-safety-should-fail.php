<?php

declare(strict_types=1);

namespace Glutamate\Tests\PHPStan;

use Glutamate\Columns\IntColumn;
use Glutamate\Columns\StringColumn;
use Illuminate\Database\Eloquent\Model;

class FailUser extends Model
{
    public static function email(): StringColumn
    {
        return StringColumn::make()->as(__FUNCTION__);
    }

    public static function age(): IntColumn
    {
        return IntColumn::make()->as(__FUNCTION__);
    }
}

// 1. Email expects string, but we pass integer 123
FailUser::where(FailUser::email(), 123);

// 2. Age expects int, but we pass string 'not-an-integer'
FailUser::where(FailUser::age(), 'not-an-integer');

// 3. Email expects string, but we pass boolean true in whereIn
FailUser::whereIn(FailUser::email(), [true]);
