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

namespace Hyperf\ServiceGovernance;

use JetBrains\PhpStorm\ArrayShape;

interface DriverInterface
{
    /**
     * @return array = [['host' => '127.0.0.1', 'port' => 9501]]
     */
    public function getNodes(
        string $uri,
        string $name,
        #[ArrayShape([
            'protocol' => 'string',
            'nodes' => [
                [
                    'host' => 'string',
                    'port' => 'int',
                    'weight' => 'int',
                ],
            ],
        ])]
        array $metadata
    ): array;

    public function isLongPolling(): bool;

    public function register(
        string $name,
        string $host,
        int $port,
        #[ArrayShape([
            'protocol' => 'string',
        ])]
        array $metadata
    ): void;

    public function isRegistered(
        string $name,
        string $host,
        int $port,
        #[ArrayShape([
            'protocol' => 'string',
        ])]
        array $metadata
    ): bool;
}
