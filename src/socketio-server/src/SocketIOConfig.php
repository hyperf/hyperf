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

namespace Hyperf\SocketIOServer;

class SocketIOConfig
{
    /**
     * @var int
     */
    private static $clientCallbackTimeout = 10000;

    /**
     * @var int
     */
    private static $pingInterval = 10000;

    /**
     * @var int
     */
    private static $pingTimeout = 100;

    /**
     * @return int
     */
    public static function getClientCallbackTimeout(): int
    {
        return self::$clientCallbackTimeout;
    }

    /**
     * @param int $clientCallbackTimeout
     */
    public static function setClientCallbackTimeout(int $clientCallbackTimeout): void
    {
        self::$clientCallbackTimeout = $clientCallbackTimeout;
    }

    /**
     * @return int
     */
    public static function getPingInterval(): int
    {
        return self::$pingInterval;
    }

    /**
     * @param int $pingInterval
     */
    public static function setPingInterval(int $pingInterval): void
    {
        self::$pingInterval = $pingInterval;
    }

    /**
     * @return int
     */
    public static function getPingTimeout(): int
    {
        return self::$pingTimeout;
    }

    /**
     * @param int $pingTimeout
     */
    public static function setPingTimeout(int $pingTimeout): void
    {
        self::$pingTimeout = $pingTimeout;
    }


}
