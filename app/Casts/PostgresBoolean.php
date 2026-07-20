<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom cast for boolean columns on PostgreSQL.
 *
 * PostgreSQL strictly requires boolean values (TRUE/FALSE) and will not
 * implicitly cast integers (1/0) to boolean. Laravel's built-in boolean
 * cast stores values as integers, which causes SQLSTATE[42804] errors
 * on PostgreSQL when PDO emulated prepares are enabled.
 *
 * This cast stores values as the strings 'true'/'false', which PostgreSQL
 * accepts as valid boolean input regardless of PDO prepare mode.
 */
class PostgresBoolean implements CastsAttributes
{
    /**
     * Cast the given value when retrieving from the database.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Prepare the given value for storage in the database.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
    }
}
