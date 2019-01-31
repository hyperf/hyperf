<?php

namespace Hyperf\Amqp;


use Hyperf\Amqp\Pool\PoolFactory;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;

class ConsumerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        return new Consumer($container, $container->get(PoolFactory::class), $container->get(StdoutLoggerInterface::class));
    }

}