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

class Bar4
{
    public function toMethodString(): string
    {
        return __METHOD__;
    }

    public function toRewriteMethodString(): string
    {
        return __METHOD__;
    }
}
