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

class Bar6
{
    public function test(): void
    {
        substr('Hello', 0) . substr('Chance', 0);
        new (get_class());
        new (substr('Bar6', 0));
        new ($this->className());
    }

    public function className(): string
    {
        return __CLASS__;
    }
}