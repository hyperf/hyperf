<?php

namespace Hyperf\Framework;


use Psr\Container\ContainerInterface;

class Hyperf
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