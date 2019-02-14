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

namespace Hyperf\Amqp;

use Hyperf\Amqp\Pool\PoolFactory;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;

class ConsumerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Consumer($container, $container->get(PoolFactory::class), $container->get(StdoutLoggerInterface::class));
    }
}
