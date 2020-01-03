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

use League\Plates\Engine;

class PlatesEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        $plates = new Engine($config['view_path'], $config['file_extension'] ?? 'php');

        return $plates->render($template, $data);
    }
}
