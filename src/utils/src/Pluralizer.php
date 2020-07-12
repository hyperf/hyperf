<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Utils;

use Doctrine\Inflector\CachedWordInflector;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English;
use Doctrine\Inflector\RulesetInflector;

class Pluralizer
{
    /**
     * @var null|Inflector
     */
    public static $inflector;

    /**
     * Uncountable word forms.
     *
     * @var array
     */
    public static $uncountable
        = [
            'audio',
            'bison',
            'cattle',
            'chassis',
            'compensation',
            'coreopsis',
            'data',
            'deer',
            'education',
            'emoji',
            'equipment',
            'evidence',
            'feedback',
            'firmware',
            'fish',
            'furniture',
            'gold',
            'hardware',
            'information',
            'jedi',
            'kin',
            'knowledge',
            'love',
            'metadata',
            'money',
            'moose',
            'news',
            'nutrition',
            'offspring',
            'plankton',
            'pokemon',
            'police',
            'rain',
            'rice',
            'series',
            'sheep',
            'software',
            'species',
            'swine',
            'traffic',
            'wheat',
        ];

    /**
     * Get the plural form of an English word.
     *
     * @param string $value
     * @param int $count
     * @return string
     */
    public static function plural($value, $count = 2)
    {
        if ((int) abs($count) === 1 || static::uncountable($value)) {
            return $value;
        }

        $plural = static::inflector()->pluralize($value);

        return static::matchCase($plural, $value);
    }

    /**
     * Get the singular form of an English word.
     *
     * @param string $value
     * @return string
     */
    public static function singular($value)
    {
        $singular = static::inflector()->singularize($value);

        return static::matchCase($singular, $value);
    }

    /**
     * Get the inflector instance.
     *
     * @return Inflector
     */
    public static function inflector()
    {
        if (is_null(static::$inflector)) {
            static::$inflector = new Inflector(
                new CachedWordInflector(new RulesetInflector(
                    English\Rules::getSingularRuleset()
                )),
                new CachedWordInflector(new RulesetInflector(
                    English\Rules::getPluralRuleset()
                ))
            );
        }

        return static::$inflector;
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
     *
     * @param string $value
     * @param string $comparison
     * @return string
     */
    protected static function matchCase($value, $comparison)
    {
        $functions = ['mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords'];

        foreach ($functions as $function) {
            if (call_user_func($function, $comparison) === $comparison) {
                return call_user_func($function, $value);
            }
        }

        return $value;
    }
}
