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

namespace Hyperf\Amqp;

class DeclaredExchanges
{
    private static array $exchanges = [];

    public static function add(string $exchange): void
    {
        self::$exchanges[$exchange] = true;
    }

    public static function remove(string $exchange): void
    {
        unset(self::$exchanges[$exchange]);
    }

    public static function has(string $exchange): bool
    {
        return isset(self::$exchanges[$exchange]);
    }
}
