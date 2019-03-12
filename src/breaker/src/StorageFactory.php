<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Breaker;

use Hyperf\Breaker\Storage\StorageInterface;
use Psr\Container\ContainerInterface;

class StorageFactory
{
    protected $container;

    protected $storages = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $name): ?StorageInterface
    {
        return $this->storages[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->storages[$name]);
    }

    public function set(string $name, StorageInterface $storage): ?StorageInterface
    {
        return $this->storages[$name] = $storage;
    }
}
