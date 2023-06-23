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

use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Finder;
use Psr\Container\ContainerInterface;

class FinderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $finder = new Finder(
            $container->get(Filesystem::class),
            (array) Blade::config('config.view_path')
        );

        $this->addNamespaces($finder, (array) Blade::config('namespaces', []));
        $this->addNamespace($finder, '__components', $directory = Blade::config('config.cache_path'));
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        return $finder;
    }

    public function addNamespace(Finder $finder, string $namespace, $hints)
    {
        foreach ($finder->getPaths() as $viewPath) {
            if (is_dir($appPath = $viewPath . '/vendor/' . $namespace)) {
                $finder->addNamespace($namespace, $appPath);
            }
        }

        $finder->addNamespace($namespace, $hints);
    }

    public function addNamespaces(Finder $finder, array $namespaces)
    {
        foreach ($namespaces as $namespace => $hints) {
            $this->addNamespace($finder, $namespace, $hints);
        }
    }
}
