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

use RuntimeException;
use Swoole\Table;

class TableManager
{
    /**
     * A container that use to store atomic.
     */
    private static array $container = [];

    /**
     * You should initialize a Table with the identifier before use it.
     */
    public static function initialize(string $identifier, int $size, float $conflictProportion = 0.2): Table
    {
        static::$container[$identifier] = new Table($size, $conflictProportion);
        return static::$container[$identifier];
    }

    /**
     * Get an initialized Table from container by the identifier.
     *
     * @throws RuntimeException when the Table with the identifier has not initialization
     */
    public static function get(string $identifier): Table
    {
        if (! isset(static::$container[$identifier])) {
            throw new RuntimeException('The Table has not initialization yet.');
        }

        return static::$container[$identifier];
    }

    /**
     * determine if the initialized Table is existed in container by the identifier ?
     */
    public static function has(string $identifier): bool
    {
        return isset(static::$container[$identifier]);
    }

    /**
     * Remove the Table by the identifier from container after used,
     * otherwise will occur memory leaks.
     */
    public static function clear(string $identifier): void
    {
        unset(static::$container[$identifier]);
    }
}
