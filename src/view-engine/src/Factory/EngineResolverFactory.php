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
use Hyperf\ViewEngine\Engine\CompilerEngine;
use Hyperf\ViewEngine\Engine\EngineResolver;
use Hyperf\ViewEngine\Engine\FileEngine;
use Hyperf\ViewEngine\Engine\PhpEngine;

class EngineResolverFactory
{
    public function __invoke(Container $container)
    {
        return EngineResolver::getInstance([
            'blade' => CompilerEngine::class,
            'php' => PhpEngine::class,
            'file' => FileEngine::class,
        ]);
    }
}
