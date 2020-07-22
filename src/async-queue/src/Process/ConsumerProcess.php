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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;

class ConsumerProcess extends AbstractProcess
{
    /**
     * @var string
     */
    protected $queue = 'default';

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var array
     */
    protected $config;

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
        if (! $this->driver instanceof DriverInterface) {
            $logger = $this->container->get(StdoutLoggerInterface::class);
            $logger->critical(sprintf('[CRITICAL] process %s is not work as expected, please check the config in [%s]', ConsumerProcess::class, 'config/autoload/queue.php'));
            return;
        }

        $this->driver->consume();
    }
}
