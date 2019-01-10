<?php

namespace Hyperf\Database;

use Psr\Container\ContainerInterface;

class Container
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    public static function setContainer(ContainerInterface $container): ContainerInterface
    {
        self::$container = $container;
        return $container;
    }
}