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
namespace Hyperf\Nats\Driver;

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractDriver implements DriverInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container, string $name, array $config)
    {
        $this->container = $container;
        $this->name = $name;
        $this->config = $config;

        $this->logger = $container->get(StdoutLoggerInterface::class);
    }
}
