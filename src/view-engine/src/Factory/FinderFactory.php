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
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Finder;

class FinderFactory
{
    public function __invoke(Container $container)
    {
        $finder = new Finder(
            $container->get(Filesystem::class),
            (array) Blade::config('config.view_path')
        );

        // register view namespace
        foreach ((array) Blade::config('namespaces', []) as $namespace => $hints) {
            foreach ($finder->getPaths() as $viewPath) {
                if (is_dir($appPath = $viewPath . '/vendor/' . $namespace)) {
                    $finder->addNamespace($namespace, $appPath);
                }
            }

            $finder->addNamespace($namespace, $hints);
        }

        return $finder;
    }
}
