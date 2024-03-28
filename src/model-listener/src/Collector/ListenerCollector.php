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

namespace Hyperf\ModelListener\Collector;

use Hyperf\Di\MetadataCollector;

class ListenerCollector extends MetadataCollector
{
    /**
     * User exposed listeners.
     *
     * These are extra user-defined events listeners may subscribe to.
     */
    protected static array $container = [];

    /**
     * Register a single listener with the model.
     */
    public static function register(string $model, string $listener): void
    {
        static::$container[$model] = array_unique(
            array_merge(
                static::$container[$model] ?? [],
                [$listener]
            )
        );
    }

    public static function setListenersForModel(string $model, array $listeners): void
    {
        static::$container[$model] = $listeners;
    }

    public static function getListenersForModel(string $model): array
    {
        return static::$container[$model] ?? [];
    }

    /**
     * Clear all registered listeners.
     */
    public static function clearListeners(): void
    {
        static::clear();
    }

    public static function clear(?string $key = null): void
    {
        if ($key) {
            foreach (static::$container as $model => $listeners) {
                if ($id = array_search($key, $listeners)) {
                    unset($listeners[$id]);
                    static::$container[$model] = array_values($listeners);
                }
            }
        } else {
            static::$container = [];
        }
    }
}
