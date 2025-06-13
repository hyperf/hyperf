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

namespace HyperfTest\Di\Stub;

class Bar
{
    public $demo;

    public $id;

    public $name;

    public function __construct(string $id, Demo $demo, $name = null)
    {
        $this->demo = $demo;
        $this->id = $id;
        $this->name = $name;
    }
}
