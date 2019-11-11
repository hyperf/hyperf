<?php

namespace Hyperf\Session\Handler;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;

class FileHandlerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $path = $config->get('session.options.path');
        $minutes = $config->get('session.options.gc_maxlifetime', 1200);
        if (! $path) {
            throw new \InvalidArgumentException('Invalid session path.');
        }
        $handler = new FileHandler($container->get(Filesystem::class), $path, $minutes);
        return $handler;
    }

}