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

use think\Template;

class ThinkEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        $engine = new Template($config);

        $engine->assign($data);

        return $engine->fetch($template);
    }
}
