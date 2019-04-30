<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Pool\SimpleImpl;

use Psr\Container\ContainerInterface;

class PoolFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Pool[]
     */
    protected $pools = [];

    /**
     * @var array
     */
    protected $configs;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addConfig(Config $config)
    {
        $this->configs[$config->getName()] = $config;
        return $this;
    }

    public function getPool(string $name, callable $callback, array $option = [])
    {
        if (! $this->hasConfig($name)) {
            $config = new Config($name, $callback, $option);
            $this->addConfig($config);
        }

        return $this->pools[$name] = make(Pool::class, [
            'callback' => $callback,
            'option' => $option,
        ]);
    }

    protected function hasConfig(string $name): bool
    {
        return isset($this->configs[$name]);
    }
}
