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

class ObserverCollector
{
    /**
     * User exposed observable events.
     *
     * These are extra user-defined events observers may subscribe to.
     *
     * @var array
     */
    protected static $observables = [];

    /**
     * Register a single observer with the model.
     */
    public static function register(string $model, string $observer): void
    {
        static::$observables[$model] = array_unique(
            array_merge(
                static::$observables[$model] ?? [],
                [$observer]
            )
        );
    }

    /**
     * Get observable mappings.
     *
     * @return array
     */
    public static function getObservables(string $model): array
    {
        return static::$observables[$model] ?? [];
    }

    /**
     * Clear all registered observers with the model.
     */
    public static function clearObservables(): void
    {
        static::$observables = [];
    }
}
