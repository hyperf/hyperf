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
use Psr\Log\LoggerInterface;

abstract class AbstractDriver implements DriverInterface
{
    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container, protected string $name, protected array $config)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }
}
