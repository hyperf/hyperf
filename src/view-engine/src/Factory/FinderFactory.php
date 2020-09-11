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
namespace Hyperf\ViewEngine\Factory;

use Hyperf\Di\Container;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Contract\FinderInterface;
use Hyperf\ViewEngine\Finder;

class FinderFactory
{
    public function __invoke(Container $container)
    {
        $finder = new Finder(
            $container->get(Filesystem::class),
            (array) Blade::config('config.view_path')
        );

        static::addNamespaces((array) Blade::config('namespaces', []), $finder);
        static::addNamespace('__components', $directory = Blade::config('config.cache_path'), $finder);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        return $finder;
    }

    public static function addNamespace($namespace, $hints, ?Finder $finder = null)
    {
        if (! $finder) {
            $finder = ApplicationContext::getContainer()->get(FinderInterface::class);
        }

        foreach ($finder->getPaths() as $viewPath) {
            if (is_dir($appPath = $viewPath . '/vendor/' . $namespace)) {
                $finder->addNamespace($namespace, $appPath);
            }
        }

        $finder->addNamespace($namespace, $hints);
    }

    public static function addNamespaces($namespaces, ?Finder $finder = null)
    {
        foreach ($namespaces as $namespace => $hints) {
            static::addNamespace($namespace, $hints, $finder);
        }
    }
}
