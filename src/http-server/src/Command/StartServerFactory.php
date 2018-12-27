<?php

namespace Hyperflex\HttpServer\Command;


use Hyperflex\Di\Annotation\Scanner;
use Psr\Container\ContainerInterface;

class StartServerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        return new StartServer($container, $container->get(Scanner::class));
    }

}