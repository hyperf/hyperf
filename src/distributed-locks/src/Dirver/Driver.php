<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DistributedLocks\Driver;

use Psr\Container\ContainerInterface;

abstract class Driver implements DriverInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $prefix;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config    = $config;
        $this->prefix    = $config['prefix'] ?? 'lock:';
    }

    /**
     * @param string $key
     * @return string
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    protected function getMutexKey(string $key)
    {
        return $this->prefix . $key;
    }
}
