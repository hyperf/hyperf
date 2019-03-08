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

namespace Hyperf\Queue\Process;

use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;
use Hyperf\Queue\Driver\DriverFactory;
use Hyperf\Queue\Driver\DriverInterface;
use Hyperf\Contract\StdoutLoggerInterface;

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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $factory = $this->container->get(DriverFactory::class);
        // @var DriverInterface $driver
        $this->driver = $factory->{$this->queue};
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
