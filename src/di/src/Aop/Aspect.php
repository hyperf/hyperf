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

namespace Hyperf\Di\Aop;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;

class Aspect
{
    /**
     * Parse the aspects that point at the class.
     */
    public static function parse(string $class): RewriteCollection
    {
        $matched = new RewriteCollection($class);
        $container = AspectCollector::list();
        foreach ($container as $type => $collection) {
            if ($type === 'classes') {
                static::parseClasses($collection, $class, $matched);
            } elseif ($type === 'annotations') {
                static::parseAnnotations($collection, $class, $matched);
            }
        }
        return $matched;
    }

    private static function parseAnnotations(array $collection, string $class, RewriteCollection $matched)
    {
        // Get the annotations of class and method.
        $annotations = AnnotationCollector::get($class);
        $classMapping = $annotations['_c'] ?? [];
        $methodMapping = value(function () use ($annotations) {
            $mapping = [];
            $methodAnnotations = $annotations['_m'] ?? [];
            foreach ($methodAnnotations as $method => $targetAnnotations) {
                $keys = array_keys($targetAnnotations);
                foreach ($keys as $key) {
                    $mapping[$key][] = $method;
                }
            }
            return $mapping;
        });
        $aspects = array_keys($collection);
        foreach ($aspects ?? [] as $aspect) {
            $rules = AspectCollector::getRule($aspect);
            foreach ($rules['annotations'] ?? [] as $rule) {
                // If exist class level annotation, then all methods should rewrite, so return an empty array directly.
                if (isset($classMapping[$rule])) {
                    return $matched->setLevel(RewriteCollection::LEVEL_CLASS);
                }
                if (isset($methodMapping[$rule])) {
                    $matched->add($methodMapping[$rule]);
                }
            }
        }
        return $matched;
    }

    private static function parseClasses(array $collection, string $class, RewriteCollection $matched)
    {
        $aspects = array_keys($collection);
        foreach ($aspects ?? [] as $aspect) {
            $rules = AspectCollector::getRule($aspect);
            foreach ($rules['classes'] ?? [] as $rule) {
                [$isMatch, $method] = static::isMatchClassRule($class, $rule);
                if ($isMatch) {
                    if ($method === null) {
                        return $matched->setLevel(RewriteCollection::LEVEL_CLASS);
                    }
                    $matched->add($method);
                }
            }
        }
    }

    /**
     * @return array [isMatch, $matchedMethods]
     */
    private static function isMatchClassRule(string $class, string $rule): array
    {
        /*
         * e.g. Foo/Bar
         * e.g. Foo/B*
         * e.g. F*o/Bar
         * e.g. Foo/Bar::method1
         * e.g. Foo/Bar::met* [WIP]
         */
        $method = null;
        if (strpos($rule, '::') !== false) {
            // @TODO Allow * for method rule.
            [$rule, $method] = explode('::', $rule);
        }
        if (strpos($rule, '*') === false && $rule === $class) {
            return [true, $method];
        }
        $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
        $pattern = "/^{$preg}$/";

        if (preg_match($pattern, $class)) {
            return [true, null];
        }

        return [false, null];
    }
}
