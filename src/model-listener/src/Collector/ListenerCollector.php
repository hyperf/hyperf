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
     * User exposed observable events.
     *
     * These are extra user-defined events observers may subscribe to.
     *
     * @var array
     */
    protected static $listeners = [];

    /**
     * Register a single observer with the model.
     */
    public static function register(string $model, string $observer): void
    {
        static::$listeners[$model] = array_unique(
            array_merge(
                static::$listeners[$model] ?? [],
                [$observer]
            )
        );
    }

    /**
     * Get observable mappings.
     *
     * @return array
     */
    public static function getListeners(string $model): array
    {
        return static::$listeners[$model] ?? [];
    }

    /**
     * Clear all registered observers with the model.
     */
    public static function clearListeners(): void
    {
        static::$listeners = [];
    }
}
