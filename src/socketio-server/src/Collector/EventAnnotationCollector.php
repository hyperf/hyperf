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

namespace Hyperf\SocketIOServer\Collector;

use Hyperf\Di\MetadataCollector;
use Hyperf\Utils\ApplicationContext;

class EventAnnotationCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function collectEvent(string $class, string $method, $value): void
    {
        if (static::has($class . '.' . $value->value)) {
            static::$container[$class][$value->value][] = [$class, $method];
        } else {
            static::$container[$class][$value->value] = [[$class, $method]];
        }
    }

    public static function collectInlineEvent(string $nsp, string $event, callable $callback): void
    {
        if (static::has("_inline.{$nsp}.{$event}")) {
            static::$container['_inline'][$nsp][$event][] = $callback;
        } else {
            static::$container['_inline'][$nsp][$event] = [$callback];
        }
    }

    /**
     * @return callable[]
     */
    public static function getEventHandler(string $nsp, string $event): array
    {
        $class = IORouter::getClass($nsp);
        /** @var callable[] $output */
        $output = [];
        foreach (static::get($class . '.' . $event, []) as [$class, $method]) {
            $output[] = [ApplicationContext::getContainer()->get($class), $method];
        }
        foreach (static::get("_inline.{$nsp}.{$event}", []) as $callback) {
            $output[] = $callback;
        }
        return $output;
    }
}
