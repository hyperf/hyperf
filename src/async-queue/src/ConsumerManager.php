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

namespace Hyperf\AsyncQueue;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;

class ConsumerManager
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function run(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $pools = $config->get('async_queue', []);

        foreach ($pools as $pool => $config) {
            if (! ($config['enable'] ?? false)) {
                continue;
            }

            $this->createProcess($pool, $config);
        }
    }

    protected function createProcess(string $pool, array $config): void
    {
        $process = new class($this->container, $pool, $config) extends AbstractProcess {
            public function __construct(
                protected ContainerInterface $container,
                protected string $pool,
                array $config
            ) {
                parent::__construct($container);
                $this->name = "queue.{$pool}";
                $this->nums = $config['processes'] ?? 1;
            }

            public function handle(): void
            {
                $driver = $this->container->get(DriverFactory::class)->get($this->pool);
                $driver->consume();
            }
        };

        ProcessManager::register($process);
    }
}
