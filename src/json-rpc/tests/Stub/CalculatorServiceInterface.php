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

interface CalculatorServiceInterface
{
    public function add(int $a, int $b);

    public function sum(IntegerValue $a, IntegerValue $b): IntegerValue;

    public function divide($value, $divider);

    public function array(int $a, int $b): array;

    public function error();

    public function getString(): ?string;

    public function callable(callable $a, ?callable $b): array;

    public function null();
}
