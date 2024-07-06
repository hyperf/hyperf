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

namespace Hyperf\View\Engine;

use duncan3dc\Laravel\BladeInstance;

class BladeEngine implements EngineInterface
{
    public function render(string $template, array $data, array $config): string
    {
        $blade = new BladeInstance($config['view_path'], $config['cache_path']);

        return $blade->render($template, $data);
    }
}
