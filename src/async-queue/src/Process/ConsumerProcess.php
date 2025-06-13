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

namespace Hyperf\AsyncQueue\Process;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;

class ConsumerProcess extends AbstractProcess
{
    protected string $queue = 'default';

    protected DriverInterface $driver;

    protected array $config;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $factory = $this->container->get(DriverFactory::class);
        $this->driver = $factory->get($this->queue);
        $this->config = $factory->getConfig($this->queue);

        $this->name = "queue.{$this->queue}";
        $this->nums = $this->config['processes'] ?? 1;
    }

    public function handle(): void
    {
        $this->driver->consume();
    }
}
