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

use Closure;
use Hyperf\Contract\Arrayable;
use Hyperf\Macroable\Macroable;
use Hyperf\Validation\Contract\Rule as RuleContract;
use Hyperf\Validation\Rules\ArrayRule;
use Hyperf\Validation\Rules\Enum;
use Hyperf\Validation\Rules\ExcludeIf;
use Hyperf\Validation\Rules\File;
use Hyperf\Validation\Rules\ImageFile;
use Hyperf\Validation\Rules\ProhibitedIf;

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

    public static function prohibitedIf($callback): ProhibitedIf
    {
        return new ProhibitedIf($callback);
    }

    public static function excludeIf($callback): ExcludeIf
    {
        return new ExcludeIf($callback);
    }

    /**
     * Apply the given rules if the given condition is truthy.
     */
    public static function when(
        bool|Closure $condition,
        array|Closure|RuleContract|string $rules,
        array|Closure|RuleContract|string $defaultRules = []
    ): ConditionalRules {
        return new ConditionalRules($condition, $rules, $defaultRules);
    }

    /**
     * Apply the given rules if the given condition is falsy.
     */
    public static function unless(
        bool|Closure $condition,
        array|Closure|RuleContract|string $rules,
        array|Closure|RuleContract|string $defaultRules = []
    ): ConditionalRules {
        return new ConditionalRules($condition, $defaultRules, $rules);
    }

    /**
     * Get an array rule builder instance.
     * @param null|mixed $keys
     */
    public static function array($keys = null): ArrayRule
    {
        return new ArrayRule(...func_get_args());
    }

    /**
     * Get an enum rule builder instance.
     */
    public static function enum(string $type): Enum
    {
        return new Enum($type);
    }

    /**
     * Get a file rule builder instance.
     */
    public static function file(): File
    {
        return new File();
    }

    /**
     * Get an image file rule builder instance.
     */
    public static function imageFile(): File
    {
        return new ImageFile();
    }
}
