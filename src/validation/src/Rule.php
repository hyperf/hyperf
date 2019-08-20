<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Validation;

use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Traits\Macroable;

class Rule
{
    use Macroable;

    /**
     * Get a dimensions constraint builder instance.
     *
     * @param array $constraints
     * @return \Hyperf\Validation\Rules\Dimensions
     */
    public static function dimensions(array $constraints = [])
    {
        return new Rules\Dimensions($constraints);
    }

    /**
     * Get a exists constraint builder instance.
     *
     * @param string $table
     * @param string $column
     * @return \Hyperf\Validation\Rules\Exists
     */
    public static function exists(string $table,string $column = 'NULL')
    {
        return new Rules\Exists($table, $column);
    }

    /**
     * Get an in constraint builder instance.
     *
     * @param array|Arrayable|string $values
     * @return \Hyperf\Validation\Rules\In
     */
    public static function in($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new Rules\In(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a not_in constraint builder instance.
     *
     * @param array|Arrayable|string $values
     * @return \Hyperf\Validation\Rules\NotIn
     */
    public static function notIn($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new Rules\NotIn(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a required_if constraint builder instance.
     *
     * @param bool|callable $callback
     * @return \Hyperf\Validation\Rules\RequiredIf
     */
    public static function requiredIf($callback)
    {
        return new Rules\RequiredIf($callback);
    }

    /**
     * Get a unique constraint builder instance.
     *
     * @param string $table
     * @param string $column
     * @return \Hyperf\Validation\Rules\Unique
     */
    public static function unique(string $table,string $column = 'NULL')
    {
        return new Rules\Unique($table, $column);
    }
}
