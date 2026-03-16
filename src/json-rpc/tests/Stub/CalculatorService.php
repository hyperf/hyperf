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

use Error;
use InvalidArgumentException;

class CalculatorService implements CalculatorServiceInterface
{
    public function add(int $a, int $b)
    {
        return $a + $b;
    }

    public function sum(IntegerValue $a, IntegerValue $b): IntegerValue
    {
        return IntegerValue::newInstance($a->getValue() + $b->getValue());
    }

    public function divide($value, $divider)
    {
        if ($divider == 0) {
            throw new InvalidArgumentException('Expected non-zero value of divider');
        }
        return $value / $divider;
    }

    public function array(int $a, int $b): array
    {
        return ['params' => [$a, $b], 'sum' => $a + $b];
    }

    public function error()
    {
        throw new Error('Not only a exception.');
    }

    public function getString(): ?string
    {
        return uniqid();
    }

    public function callable(callable $a, ?callable $b): array
    {
        return [[], null];
    }

    public function null()
    {
        return null;
    }
}
