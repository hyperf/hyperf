<?php

namespace Hyperf\WebSocketServer\SocketIO;

use Hyperf\Di\MetadataCollector;

class EventAnnotationCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function collectEvent(string $class, string $method, $value): void
    {
        static::$container[$class][$value->value] = $method;
    }

    public static function getEventHandler(string $class, string $event): ?string
    {
        return static::get($class . '.' . $event);
    }

}
