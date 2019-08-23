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

namespace Hyperf\ModelListener\Collector;

class ListenerCollector
{
    /**
     * User exposed listeners.
     *
     * These are extra user-defined events listeners may subscribe to.
     *
     * @var array
     */
    protected static $listeners = [];

    /**
     * Register a single listener with the model.
     */
    public static function register(string $model, string $listener): void
    {
        static::$listeners[$model] = array_unique(
            array_merge(
                static::$listeners[$model] ?? [],
                [$listener]
            )
        );
    }

    public static function setListeners(string $model, array $listeners): void
    {
        static::$listeners[$model] = $listeners;
    }

    public static function getListeners(string $model): array
    {
        return static::$listeners[$model] ?? [];
    }

    /**
     * Clear all registered listeners.
     */
    public static function clearListeners(): void
    {
        static::$listeners = [];
    }
}
