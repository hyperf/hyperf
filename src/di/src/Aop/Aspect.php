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
namespace Hyperf\Di\Aop;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;

use function Hyperf\Support\value;

class Aspect
{
    /**
     * Parse the aspects that point at the class.
     */
    public static function parse(string $class): RewriteCollection
    {
        $rewriteCollection = new RewriteCollection($class);
        $container = AspectCollector::list();
        foreach ($container as $type => $collection) {
            if ($type === 'classes') {
                static::parseClasses($collection, $class, $rewriteCollection);
            } elseif ($type === 'annotations') {
                static::parseAnnotations($collection, $class, $rewriteCollection);
            }
        }
        return $rewriteCollection;
    }

    /**
     * @return array [isMatch, $matchedMethods]
     */
    public static function isMatchClassRule(string $target, string $rule): array
    {
        /*
         * e.g. Foo/Bar
         * e.g. Foo/B*
         * e.g. F*o/Bar
         * e.g. Foo/Bar::method
         * e.g. Foo/Bar::met*
         */
        $ruleMethod = null;
        $ruleClass = $rule;
        $method = null;
        $class = $target;

        if (str_contains($rule, '::')) {
            [$ruleClass, $ruleMethod] = explode('::', $rule);
        }
        if (str_contains($target, '::')) {
            [$class, $method] = explode('::', $target);
        }

        if ($method == null) {
            if (! str_contains($ruleClass, '*')) {
                /*
                 * Match [rule] Foo/Bar::ruleMethod [target] Foo/Bar [return] true,ruleMethod
                 * Match [rule] Foo/Bar [target] Foo/Bar [return] true,null
                 * Match [rule] FooBar::rule*Method [target] Foo/Bar [return] true,rule*Method
                 */
                if ($ruleClass === $class) {
                    return [true, $ruleMethod];
                }

                return [false, null];
            }

            /**
             * Match [rule] Foo*Bar::ruleMethod [target] Foo/Bar [return] true,ruleMethod
             * Match [rule] Foo*Bar [target] Foo/Bar [return] true,null.
             */
            $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $ruleClass);
            $pattern = "#^{$preg}$#";

            if (preg_match($pattern, $class)) {
                return [true, $ruleMethod];
            }

            return [false, null];
        }

        if (! str_contains($rule, '*')) {
            /*
             * Match [rule] Foo/Bar::ruleMethod [target] Foo/Bar::ruleMethod [return] true,ruleMethod
             * Match [rule] Foo/Bar [target] Foo/Bar::ruleMethod [return] false,null
             */
            if ($ruleClass === $class && ($ruleMethod === null || $ruleMethod === $method)) {
                return [true, $method];
            }

            return [false, null];
        }

        /*
         * Match [rule] Foo*Bar::ruleMethod [target] Foo/Bar::ruleMethod [return] true,ruleMethod
         * Match [rule] FooBar::rule*Method [target] Foo/Bar::ruleMethod [return] true,rule*Method
         */
        if ($ruleMethod) {
            $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
            $pattern = "#^{$preg}$#";
            if (preg_match($pattern, $target)) {
                return [true, $method];
            }
        } else {
            /**
             * Match [rule] Foo*Bar [target] Foo/Bar::ruleMethod [return] true,null.
             */
            $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
            $pattern = "#^{$preg}$#";
            if (preg_match($pattern, $class)) {
                return [true, $method];
            }
        }

        return [false, null];
    }

    public static function isMatch(string $class, string $method, string $rule): bool
    {
        [$isMatch] = self::isMatchClassRule($class . '::' . $method, $rule);

        return $isMatch;
    }

    private static function parseAnnotations(array $collection, string $class, RewriteCollection $rewriteCollection): void
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
        foreach ($aspects as $aspect) {
            $rules = AspectCollector::getRule($aspect);
            foreach ($rules['annotations'] ?? [] as $rule) {
                // If exist class level annotation, then all methods should rewrite, so return an empty array directly.
                if (isset($classMapping[$rule])) {
                    $rewriteCollection->setLevel(RewriteCollection::CLASS_LEVEL);
                    return;
                }
                if (isset($methodMapping[$rule])) {
                    $rewriteCollection->add($methodMapping[$rule]);
                }
            }
        }
    }

    private static function parseClasses(array $collection, string $class, RewriteCollection $rewriteCollection): void
    {
        $aspects = array_keys($collection);
        foreach ($aspects as $aspect) {
            $rules = AspectCollector::getRule($aspect);
            foreach ($rules['classes'] ?? [] as $rule) {
                [$isMatch, $method] = static::isMatchClassRule($class, $rule);
                if ($isMatch) {
                    if ($method === null) {
                        $rewriteCollection->setLevel(RewriteCollection::CLASS_LEVEL);
                        return;
                    }
                    $rewriteCollection->add($method);
                }
            }
        }
    }
}
