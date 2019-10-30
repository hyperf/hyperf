<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Utils;

use Psr\Container\ContainerInterface;

class ApplicationContext
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }

    public static function setContainer(ContainerInterface $container): ContainerInterface
    {
        self::$container = $container;
        return $container;
    }
}
