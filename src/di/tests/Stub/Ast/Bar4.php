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

    public function toRewriteMethodString1(int $count): string
    {
        return __METHOD__;
    }

    public function toRewriteMethodString2(int $count, string ...$params): string
    {
        return __METHOD__;
    }


    public function toRewriteMethodString3(int &$count): string
    {
        return __METHOD__;
    }
}
