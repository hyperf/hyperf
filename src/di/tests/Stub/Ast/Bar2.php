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

namespace HyperfTest\Di\Stub\Ast;

class Bar2 extends Bar
{
    public function __construct(int $id)
    {
        parent::__construct($id);
    }

    public static function build()
    {
        return parent::$items;
    }
}
