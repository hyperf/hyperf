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

namespace Hyperf\Stringable;

use Countable;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;

class Pluralizer
{
    /**
     * Uncountable word forms.
     */
    public static array $uncountable
        = [
            'recommended',
            'related',
        ];

    /**
     * The language that should be used by the inflector.
     */
    protected static string $language = Language::ENGLISH;

    protected static ?Inflector $inflector = null;

    /**
     * Get the plural form of an English word.
     */
    public static function plural(string $value, array|Countable|int $count = 2): string
    {
        if (is_countable($count)) {
            $count = count($count);
        }

        if ((int) abs($count) === 1 || static::uncountable($value) || preg_match('/^(.*)[A-Za-z0-9\x{0080}-\x{FFFF}]$/u', $value) == 0) {
            return $value;
        }

        $plural = static::getInflector()->pluralize($value);

        return static::matchCase($plural, $value);
    }

    /**
     * Get the singular form of an English word.
     */
    public static function singular(string $value): string
    {
        $singular = static::getInflector()->singularize($value);

        return static::matchCase($singular, $value);
    }

    public static function setInflector(?Inflector $inflector): void
    {
        static::$inflector = $inflector;
    }

    /**
     * Get the inflector instance.
     */
    public static function getInflector(): Inflector
    {
        if (is_null(static::$inflector)) {
            static::$inflector = InflectorFactory::createForLanguage(static::$language)->build();
        }

        return static::$inflector;
    }

    public static function useLanguage(string $language): void
    {
        static::$language = $language;

        static::$inflector = null;
    }

    /**
     * Determine if the given value is uncountable.
     *
     * @param string $value
     * @return bool
     */
    protected static function uncountable($value)
    {
        return in_array(strtolower($value), static::$uncountable);
    }

    /**
     * Attempt to match the case on two strings.
     */
    protected static function matchCase(string $value, string $comparison): string
    {
        $functions = ['mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords'];

        foreach ($functions as $function) {
            if ($function($comparison) === $comparison) {
                return $function($value);
            }
        }

        return $value;
    }
}
