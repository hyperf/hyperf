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
use Hyperf\SocketIOServer\Annotation\Event;

class EventAnnotationCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function collectEvent(string $class, string $method, Event $value): void
    {
        if (static::has($class . '.' . $value->event)) {
            static::$container[$class][$value->event][] = [$class, $method];
        } else {
            static::$container[$class][$value->event] = [[$class, $method]];
        }
    }
}
