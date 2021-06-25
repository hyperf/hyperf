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

interface ServiceGovernanceInterface
{
    public function getNodes(): array;

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
