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

namespace Hyperf\ViewEngine;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\ViewEngine\Compiler\CompilerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class Blade.
 *
 * @method static array getClassComponentAliases()
 * @method static array getCustomDirectives()
 * @method static array getExtensions()
 * @method static bool check(string $name, array ...$parameters)
 * @method static string compileString(string $value)
 * @method static string getPath()
 * @method static string stripParentheses(string $expression)
 * @method static void aliasComponent(string $path, null|string $alias = null)
 * @method static void aliasInclude(string $path, null|string $alias = null)
 * @method static void compile(null|string $path = null)
 * @method static void component(string $class, null|string $alias = null, string $prefix = '')
 * @method static void components(array $components, string $prefix = '')
 * @method static void directive(string $name, callable $handler)
 * @method static void extend(callable $compiler)
 * @method static void if (string $name, callable $callback)
 * @method static void include (string $path, string|null $alias = null)
 * @method static void precompiler(callable $precompiler)
 * @method static void setEchoFormat(string $format)
 * @method static void setPath(string $path)
 * @method static void withDoubleEncoding()
 * @method static void withoutComponentTags()
 * @method static void withoutDoubleEncoding()
 */
class Blade
{
    protected static ?ContainerInterface $container = null;

    public static function __callStatic($method, $args)
    {
        return static::resolve()->{$method}(...$args);
    }

    public static function resolve(): CompilerInterface
    {
        return static::container()
            ->get(CompilerInterface::class);
    }

    public static function container()
    {
        return static::$container ?: static::$container = ApplicationContext::getContainer();
    }

    public static function config($key, $default = '')
    {
        $key = 'view.' . $key;

        return static::container()
            ->get(ConfigInterface::class)
            ->get($key, $default);
    }
}
