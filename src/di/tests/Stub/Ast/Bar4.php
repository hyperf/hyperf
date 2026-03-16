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

    /**
     * To test method parameters (with type declaration in use).
     */
    public function toRewriteMethodString1(int $count): string
    {
        return __METHOD__;
    }

    /**
     * To test passing by references.
     */
    public function toRewriteMethodString2(int &$count): string
    {
        return __METHOD__;
    }

    /**
     * To test variadic parameters (without type declaration).
     */
    public function toRewriteMethodString3(...$params): string
    {
        return __METHOD__;
    }

    /**
     * To test variadic parameters with type declaration.
     */
    public function toRewriteMethodString4(int &$count, string ...$params): string
    {
        return __METHOD__;
    }
}
