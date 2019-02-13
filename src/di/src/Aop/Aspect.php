<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Aop;

use Hyperf\Di\Annotation\AspectCollector;

class Aspect
{
    /**
     * Parse the aspects that point at the class.
     */
    public static function parse(string $class): array
    {
        $matched = [];
        $container = AspectCollector::getContainer();
        foreach ($container as $type => $collection) {
            if ($type === 'classes') {
                static::parseClasses($collection, $class, $matched);
            }
            // @TODO Parse annotations aspects.
        }
        return $matched;
    }

    private static function parseClasses(array $collection, string $class, array &$matched)
    {
        $aspects = array_keys($collection);
        foreach ($aspects ?? [] as $aspect) {
            $rules = AspectCollector::getRule($aspect);
            foreach ($rules['classes'] ?? [] as $rule) {
                [$isMatch, $classes] = static::isMatch($class, $rule);
                if ($isMatch) {
                    $matched[$aspect] = $classes;
                    break;
                }
            }
        }
    }

    /**
     * @return array [isMatch, $matchedMethods]
     */
    private static function isMatch(string $class, string $rule): array
    {
        if (strpos($rule, '::') !== false) {
            // @TODO Allow * for method rule.
            [$rule, $method] = explode('::', $rule);
        }
        if (strpos($rule, '*') === false && $rule === $class) {
            return [true, isset($method) && $method ? [$method] : []];
        }
        $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
        $pattern = "/^${preg}$/";

        if (preg_match($pattern, $class)) {
            return [true, []];
        }

        return [false, []];
    }
}
