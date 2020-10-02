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

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

interface DriverInterface extends CacheInterface
{
    public function __construct(ContainerInterface $container, array $config);

    /**
     * Return state of existence and data at the same time.
     * @param null|mixed $default
     */
    public function fetch(string $key, $default = null): array;

    /**
     * Clean up data of the same prefix.
     */
    public function clearPrefix(string $prefix): bool;
}
