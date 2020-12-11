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

use Hyperf\View\Exception\EngineNotFindException;

class NoneEngine
{
    public function __construct()
    {
        throw new EngineNotFindException('No engine available, You can use Blade, Smarty, Twig, Plates and ThinkTemplate.');
    }
}
