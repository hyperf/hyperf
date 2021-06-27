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
namespace Hyperf\ServiceGovernanceNacos;

use Hyperf\ServiceGovernance\DriverInterface;

class NacosDriver implements DriverInterface
{
    public function getNodes(string $uri, string $name, array $metadata): array
    {
        // TODO: Implement getNodes() method.
    }

    public function register(string $name, string $host, int $port, array $metadata): void
    {
        // TODO: Implement register() method.
    }

    public function isRegistered(string $name, string $host, int $port, array $metadata): bool
    {
        // TODO: Implement isRegistered() method.
    }
}
