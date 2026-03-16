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

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigEngine implements EngineInterface
{
    public function render(string $template, array $data, array $config): string
    {
        $loader = new FilesystemLoader($config['view_path']);
        $twig = new Environment($loader, ['cache' => $config['cache_path']]);

        if ($suffix = $config['template_suffix'] ?? '') {
            $template .= $suffix;
        }

        return $twig->render($template, $data);
    }
}
