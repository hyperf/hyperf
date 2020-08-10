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

use Hyperf\Jet\Exception\ClientException;

class ServiceManager
{
    public const NODES = 'nodes';

    /**
     * @var array
     */
    protected static $services = [];

    public static function getService(string $service, string $protocol): array
    {
        return static::$services[static::buildKey($service, $protocol)] ?? [];
    }

    public static function isServiceRegistered(string $service, string $protocol): bool
    {
        return isset(static::$services[static::buildKey($service, $protocol)]);
    }

    public static function register(string $service, string $protocol, array $metadata = [])
    {
        if (! ProtocolManager::isProtocolRegistered($protocol)) {
            throw new ClientException(sprintf('The protocol %s does not register to %s yet.', ProtocolManager::class, $protocol));
        }
        static::$services[static::buildKey($service, $protocol)] = $metadata;
    }

    public static function deregister(string $service, string $protocol): void
    {
        unset(static::$services[static::buildKey($service, $protocol)]);
    }

    private static function buildKey(string $service, string $protocol): string
    {
        return $service . '@' . $protocol;
    }
}
