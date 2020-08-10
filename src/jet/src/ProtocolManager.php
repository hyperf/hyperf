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
namespace Hyperf\Jet;

class ProtocolManager
{
    public const DATA_FORMATTER = 'df';

    public const PATH_GENERATOR = 'pg';

    public const TRANSPORTER = 't';

    public const PACKER = 'p';

    /**
     * @var array
     */
    protected static $protocols = [];

    public static function getProtocol(string $protocolName): array
    {
        return static::$protocols[$protocolName] ?? [];
    }

    public static function isProtocolRegistered(string $protocolName): bool
    {
        return isset(static::$protocols[$protocolName]);
    }

    public static function register(string $protocolName, array $metadatas): void
    {
        static::$protocols[$protocolName] = $metadatas;
    }

    public static function deregister(string $protocolName): void
    {
        unset(static::$protocols[$protocolName]);
    }
}
