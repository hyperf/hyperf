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

use League\Plates\Engine;

class PlatesEngine implements EngineInterface
{
    public function render(string $template, array $data, array $config): string
    {
        $plates = new Engine($config['view_path'], $config['file_extension'] ?? 'php');

        return $plates->render($template, $data);
    }
}
