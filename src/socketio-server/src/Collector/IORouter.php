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
namespace Hyperf\SocketIOServer\Collector;

use Hyperf\Di\MetadataCollector;
use Hyperf\SocketIOServer\Exception\RouteNotFoundException;
use Hyperf\Utils\ApplicationContext;

class IORouter extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function addNamespace(string $nsp, string $className)
    {
        static::set('forward.' . $nsp, $className);
        static::set('backward.' . $className, $nsp);
    }

    public static function getNamespace(string $className)
    {
        return static::get('backward.' . $className, '/');
    }

    public static function getClass(string $nsp)
    {
        return static::get('forward.' . $nsp);
    }

    public static function getAdapter(string $nsp)
    {
        $class = static::getClass($nsp);
        if (! $class) {
            throw new RouteNotFoundException("Namespace {$nsp} is not registered in the router.");
        }
        return ApplicationContext::getContainer()->get($class)->getAdapter();
    }
}
