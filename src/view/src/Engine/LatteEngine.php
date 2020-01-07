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

namespace Hyperf\View\Engine;

use Latte\Engine;
use Latte\Loaders\FileLoader;

class LatteEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        $loader = new FileLoader($config['view_path']);

        $latte = new Engine();
        $latte->setLoader($loader);
        $latte->setTempDirectory($config['cache_path']);

        return $latte->renderToString($template, $data);
    }
}
