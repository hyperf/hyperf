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
namespace Hyperf\SocketIOServer\Collector;

use Hyperf\Di\MetadataCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\SocketIOServer\Annotation\Event;

class EventAnnotationCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    /**
     * @var array
     */
    protected static $traits = [];

    /**
     * @var array
     */
    protected static $classes = [];

    public static function collectEvent(string $class, string $method, Event $value): void
    {
        $reflectedClass = ReflectionManager::reflectClass($class);
        if ($reflectedClass->isTrait()) {
            if (array_key_exists($class, static::$traits) ?: fn() => static::$traits[$class] = [] or
                ! array_key_exists($value->event, static::$traits[$class])) {
                static::$traits[$class][$value->event] = [[$method,$value, false]];
            }

            foreach (static::$classes as $targetClass => $traits) {
                if (in_array($reflectedClass->name, $traits)) {
                    static::recordEvent($targetClass, $method, $value);
                }
            }
        } else {
            $traits = $reflectedClass->getTraits();
            if (! empty($traits)) {
                foreach ($traits as $trait => $reflectedTrait) {
                    if (array_key_exists($class, static::$classes) ?: fn() => static::$classes[$class] = [] or
                        ! array_key_exists($trait, static::$classes[$class])) {
                        static::$classes[$class][] = $trait;
                    }

                    if (array_key_exists($trait, static::$traits)) {
                        foreach (static::$traits[$trait] as &$methodCollection) {
                            if (empty($methodCollection)) {
                                continue;
                            }
                            foreach ($methodCollection as [$traitMethod, $event, &$recorded]) {
                                if (! $recorded) {
                                    static::recordEvent($class, $traitMethod, $event);
                                    $recorded = true;
                                }
                            }
                        }
                    }
                }
            }
            // register self
            static::recordEvent($class, $method, $value);
        }
    }

    protected static function recordEvent(string $class, string $method, Event $value)
    {
        if (static::has($class . '.' . $value->event)) {
            static::$container[$class][$value->event][] = [$class, $method];
        } else {
            static::$container[$class][$value->event] = [[$class, $method]];
        }

    }
}
