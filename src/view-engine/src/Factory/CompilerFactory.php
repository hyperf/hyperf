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
use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Compiler\BladeCompiler;

class CompilerFactory
{
    public function __invoke(Container $container)
    {
        $blade = new BladeCompiler(
            $container->get(Filesystem::class),
            Blade::config('config.cache_path')
        );

        // register view components
        foreach ((array) Blade::config('components', []) as $alias => $class) {
            $blade->component($class, $alias);
        }

        $blade->setComponentAutoload((array) Blade::config('autoload', ['classes' => [], 'components' => []]));

        return $blade;
    }
}
