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

namespace HyperfTest\JsonRpc\Stub;

class IntegerValue
{
    /**
     * @var int
     */
    private $value;

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    /**
     * @return IntegerValue
     */
    public static function newInstance(int $value)
    {
        $obj = new IntegerValue();
        $obj->value = $value;
        return $obj;
    }
}
