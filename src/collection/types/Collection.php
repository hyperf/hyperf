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
use Hyperf\Collection\Collection;

use function PHPStan\Testing\assertType;

$numbers = Collection::make([1, 2, 3]);
assertType('Hyperf\Collection\Collection<int, int>', $numbers);

$floatResults = $numbers->map(static fn (int $value, int $key): float => ($value + $key) / 2);
assertType('Hyperf\Collection\Collection<int, float>', $floatResults);

$withNamedKeys = Collection::make(['foo' => 1, 'bar' => 2]);
assertType('Hyperf\Collection\Collection<string, int>', $withNamedKeys);

$mapWithKeys = $withNamedKeys->mapWithKeys(static fn (int $value, string $key): array => ['mapped-' . $key => $value * 10]);
assertType('Hyperf\Collection\Collection<string, int>', $mapWithKeys);

$mapToDictionary = $withNamedKeys->mapToDictionary(static fn (int $value, string $key): array => [$key => $value / 10.0]);
assertType('Hyperf\Collection\Collection<string, array<int, float>>', $mapToDictionary);

$mapToGroups = $withNamedKeys->mapToGroups(static fn (int $value, string $key): array => ['group-' . $key => $value / 10.0]);
assertType('Hyperf\Collection\Collection<string, Hyperf\Collection\Collection<int, float>>', $mapToGroups);

$flatMapped = $withNamedKeys->flatMap(static fn (int $value, string $key): array => [$key => $value * 10]);
assertType('Hyperf\Collection\Collection<string, int>', $flatMapped);

$keys = $withNamedKeys->keys();
assertType('Hyperf\Collection\Collection<int, string>', $keys);

$zipped = $numbers->zip([1.5]);
assertType('Hyperf\Collection\Collection<int, Hyperf\Collection\Collection<int, float|int>>', $zipped);

$padded = $numbers->pad(5, 0.5);
assertType('Hyperf\Collection\Collection<int, float|int>', $padded);

$onlyFoo = $withNamedKeys->only(['foo']);
assertType('Hyperf\Collection\Collection<string, int>', $onlyFoo);

$exceptFoo = $withNamedKeys->except(['foo']);
assertType('Hyperf\Collection\Collection<string, int>', $exceptFoo);
