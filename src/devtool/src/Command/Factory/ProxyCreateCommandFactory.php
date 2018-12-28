<?php

namespace Hyperf\Devtool\Command\Factory;

use Hyperf\Devtool\Command\ProxyCreateCommand;
use Hyperf\Di\Annotation\Scanner;
use Psr\Container\ContainerInterface;

class ProxyCreateCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ProxyCreateCommand($container, $container->get(Scanner::class));
    }
}