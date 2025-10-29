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
use Hyperf\Collection\LazyCollection;

use function PHPStan\Testing\assertType;

$lazyNumbers = LazyCollection::make([1, 2, 3]);
assertType('Hyperf\Collection\LazyCollection<int, int>', $lazyNumbers);

$lazyMapped = $lazyNumbers->map(static fn (int $value, int $key): float => ($value + $key) / 2);
assertType('Hyperf\Collection\LazyCollection<int, float>', $lazyMapped);

$lazyMapWithKeys = $lazyNumbers->mapWithKeys(static fn (int $value, int $key): array => ['key-' . $key => $value * 10]);
assertType('Hyperf\Collection\LazyCollection<string, int>', $lazyMapWithKeys);

$lazyMapToDictionary = $lazyNumbers->mapToDictionary(static fn (int $value, int $key): array => [$key => $value / 10.0]);
assertType('Hyperf\Collection\LazyCollection<int, array<int, float>>', $lazyMapToDictionary);

$lazyMapToGroups = $lazyNumbers->mapToGroups(static fn (int $value, int $key): array => ['group-' . $key => $value / 10.0]);
assertType('Hyperf\Collection\LazyCollection<string, Hyperf\Collection\LazyCollection<int, float>>', $lazyMapToGroups);

$lazyFlatMap = $lazyNumbers->flatMap(static fn (int $value, int $key): array => [$key => $value * 10]);
assertType('Hyperf\Collection\LazyCollection<int, int>', $lazyFlatMap);

$lazyCombined = $lazyNumbers->combine([0.1, 0.2, 0.3]);
assertType('Hyperf\Collection\LazyCollection<int, float>', $lazyCombined);

$lazyZipped = $lazyNumbers->zip([1.5]);
assertType('Hyperf\Collection\LazyCollection<int, Hyperf\Collection\LazyCollection<int, float|int>>', $lazyZipped);
