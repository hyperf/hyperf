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

namespace HyperfTest\Config\Stub;

class Foo
{
    public $id;

    public function __construct($id = 0)
    {
        $this->id = $id;
    }

    public static function make()
    {
        return new self(2);
    }
}
