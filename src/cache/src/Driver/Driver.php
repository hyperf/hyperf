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

use Hyperf\Contract\PackerInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Cache\Packer\PhpSerializer;

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

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;

        $packerClass = $config['packer'] ?? PhpSerializer::class;
        $this->packer = $container->get($packerClass);
    }
}
