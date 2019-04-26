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

namespace Hyperf\Cache\Driver;

use Hyperf\Utils\Packer\PhpSerializer;
use Hyperf\Contract\PackerInterface;
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
     * @var PackerInterface
     */
    protected $packer;

    /**
     * @var string
     */
    protected $prefix;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
        $this->prefix = $config['prefix'] ?? 'cache:';

        $packerClass = $config['packer'] ?? PhpSerializer::class;
        $this->packer = $container->get($packerClass);
    }

    protected function getCacheKey(string $key)
    {
        return $this->prefix . $key;
    }
}
