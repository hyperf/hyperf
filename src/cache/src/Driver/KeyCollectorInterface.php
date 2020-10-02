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
namespace Hyperf\Cache\Driver;

interface KeyCollectorInterface
{
    public function addKey(string $collector, string $key): bool;

    public function keys(string $collector): array;

    public function delKey(string $collector, ...$key): bool;
}
