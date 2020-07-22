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
namespace Hyperf\Memory;

use Swoole\Atomic;

class AtomicManager
{
    /**
     * A container that use to store atomic.
     *
     * @var array
     */
    private static $container = [];

    /**
     * You should initialize a Atomic with the identifier before use it.
     * @param mixed $value
     */
    public static function initialize(string $identifier, $value = 0): void
    {
        static::$container[$identifier] = new Atomic($value);
    }

    /**
     * Get a initialized Atomic from container by the identifier.
     *
     * @throws \RuntimeException when the Atomic with the identifier has not initialization
     */
    public static function get(string $identifier): Atomic
    {
        if (! isset(static::$container[$identifier])) {
            throw new \RuntimeException('The Atomic has not initialization yet.');
        }

        return static::$container[$identifier];
    }

    /**
     * Remove the Atomic by the identifier from container after used,
     * otherwise will occur memory leaks.
     */
    public static function clear(string $identifier): void
    {
        unset(static::$container[$identifier]);
    }
}
