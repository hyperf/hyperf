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
    protected string $pool = 'default';

    protected DriverInterface $driver;

    protected array $config;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        // compatible with older versions, will be removed in v3.2, use `$pool` instead.
        if (property_exists($this, 'queue')) {
            if ($container->has(StdoutLoggerInterface::class)) {
                $container->get(StdoutLoggerInterface::class)->warning(sprintf('The property "%s::$queue" is deprecated since v3.1 and will be removed in v3.2, use "%s::$pool" instead.', self::class, self::class));
            }
            $this->pool = $this->queue;
        }

        $factory = $this->container->get(DriverFactory::class);
        $this->driver = $factory->get($this->pool);
        $this->config = $factory->getConfig($this->pool);

        $this->name = "queue.{$this->pool}";
        $this->nums = $this->config['processes'] ?? 1;
    }

    public function handle(): void
    {
        $this->driver->consume();
    }
}
