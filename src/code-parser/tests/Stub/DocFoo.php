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

namespace HyperfTest\CodeParser\Stub;

class DocFoo
{
    public function getBuiltinInt(): ?int
    {
        return 1;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return uniqid();
    }

    /**
     * @return int|string
     */
    public function getStringOrInt()
    {
        return uniqid();
    }

    /**
     * @return DocFoo
     */
    public function getSelf()
    {
        return $this;
    }

    /**
     * @return bool|DocFoo
     */
    public function getSelfOrNot()
    {
        return $this;
    }
}
