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
namespace Hyperf\SocketIOServer\Collector;

use Hyperf\Di\MetadataCollector;
use Hyperf\SocketIOServer\Exception\RouteNotFoundException;
use Hyperf\SocketIOServer\NamespaceInterface;
use Hyperf\Utils\ApplicationContext;

class SocketIORouter extends MetadataCollector
{
    protected static array $container = [];

    public static function addNamespace(string $nsp, string $className)
    {
        static::set('forward.' . $nsp, $className);
        static::set('backward.' . $className, $nsp);
    }

    public static function clear(string $key = null): void
    {
        if ($key !== null) {
            parent::clear('backward.' . $key);
            foreach (static::$container['forward'] ?? [] as $nsp => $value) {
                if ($value == $key) {
                    unset(static::$container['forward'][$nsp]);
                }
            }
        }
        parent::clear($key);
    }

    public static function getNamespace(string $className)
    {
        return static::get('backward.' . $className, '/');
    }

    public static function getClassName(string $nsp)
    {
        return static::get('forward.' . $nsp);
    }

    public static function getAdapter(string $nsp)
    {
        $class = static::getClassName($nsp);
        if (! $class) {
            throw new RouteNotFoundException("namespace {$nsp} is not registered in the router.");
        }
        if (! ApplicationContext::getContainer()->has($class)) {
            throw new RouteNotFoundException("namespace {$nsp} cannot be instantiated.");
        }

        $instance = ApplicationContext::getContainer()->get($class);

        if (! $instance instanceof NamespaceInterface) {
            throw new RouteNotFoundException("namespace {$nsp} must be an instance of NamespaceInterface");
        }

        return $instance->getAdapter();
    }
}
