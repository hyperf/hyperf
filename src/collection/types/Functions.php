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
use function Hyperf\Collection\collect;
use function PHPStan\Testing\assertType;

$collected = collect(['foo' => 1, 'bar' => 2]);
assertType('Hyperf\Collection\Collection<string, int>', $collected);

$mapped = $collected->map(static fn (int $value, string $key): float => $value * 1.5);
assertType('Hyperf\Collection\Collection<string, float>', $mapped);
