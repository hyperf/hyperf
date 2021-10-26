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
namespace Hyperf\Redis\Lua;

interface ScriptInterface
{
    public function getScript(): string;

    public function format($data);

    public function eval(array $arguments = [], $sha = true);
}
