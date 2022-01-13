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

interface DriverInterface
{
    /**
     * @param $metadata = [
     *     'protocol' => 'default',
     * ]
     * @return array = [['host' => '127.0.0.1', 'port' => 9501]]
     */
    public function getNodes(string $uri, string $name, array $metadata): array;

    /**
     * @param $metadata = [
     *     'protocol' => 'default',
     * ]
     */
    public function register(string $name, string $host, int $port, array $metadata): void;

    /**
     * @param $metadata = [
     *     'protocol' => 'default',
     * ]
     */
    public function isRegistered(string $name, string $host, int $port, array $metadata): bool;
}
