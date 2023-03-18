<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Validation;

use Hyperf\Contract\Arrayable;
use Hyperf\Macroable\Macroable;

class Rule
{
    use Macroable;

    /**
     * Get a dimensions constraint builder instance.
     */
    public static function dimensions(array $constraints = []): Rules\Dimensions
    {
        return new Rules\Dimensions($constraints);
    }

    /**
     * Get a exists constraint builder instance.
     */
    public static function exists(string $table, string $column = 'NULL'): Rules\Exists
    {
        return new Rules\Exists($table, $column);
    }

    /**
     * Get an in constraint builder instance.
     */
    public static function in(mixed $values): Rules\In
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new Rules\In(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a not_in constraint builder instance.
     */
    public static function notIn(mixed $values): Rules\NotIn
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new Rules\NotIn(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a required_if constraint builder instance.
     */
    public static function requiredIf(bool|callable $callback): Rules\RequiredIf
    {
        return new Rules\RequiredIf($callback);
    }

    /**
     * Get a unique constraint builder instance.
     */
    public static function unique(string $table, string $column = 'NULL'): Rules\Unique
    {
        return new Rules\Unique($table, $column);
    }
}
