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

namespace HyperfTest\Collection;

use Hyperf\Collection\Collection;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Collection\collect;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CollectionTest extends TestCase
{
    public function testOperatorForWhere(): void
    {
        $col = new Collection([['id' => 1, 'name' => 'Hyperf'], ['id' => 2, 'name' => 'HyperfCloud']]);

        $res = $col->where('id', 1);
        $this->assertSame(1, $res->count());
        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], $res->shift());

        $res = $col->where('id', '=', 2);
        $this->assertSame(1, $res->count());
        $this->assertSame(['id' => 2, 'name' => 'HyperfCloud'], $res->shift());
    }

    public function testRandom(): void
    {
        $col = new Collection([['id' => 1, 'name' => 'Hyperf'], ['id' => 2, 'name' => 'HyperfCloud']]);

        $res = $col->random();
        $this->assertIsArray($res);

        $res = $col->random(1);
        $this->assertInstanceOf(Collection::class, $res);
    }

    public function testFlatten(): void
    {
        $collection = new Collection([
            'item' => [
                'name' => 'Hyperf',
            ],
            'it' => [
                'id' => $uuid = uniqid('', true),
            ],
        ]);

        $this->assertSame(['Hyperf', $uuid], $collection->flatten()->toArray());
    }

    public function testCollectionAverage(): void
    {
        $col = new Collection([]);
        $this->assertNull($col->avg());
    }

    public function testContainsOneItem(): void
    {
        $this->assertFalse((new Collection([]))->containsOneItem());
        $this->assertTrue((new Collection([1]))->containsOneItem());
        $this->assertFalse((new Collection([1, 2]))->containsOneItem());
    }

    public function testDoesntContain(): void
    {
        $col = new Collection([1, 3, 5]);

        $this->assertFalse($col->doesntContain(1));
        $this->assertFalse($col->doesntContain('1'));
        $this->assertTrue($col->doesntContain(2));
        $this->assertTrue($col->doesntContain('2'));

        $col = new Collection(['1']);
        $this->assertFalse($col->doesntContain('1'));
        $this->assertFalse($col->doesntContain(1));

        $col = new Collection([null]);
        $this->assertFalse($col->doesntContain(false));
        $this->assertFalse($col->doesntContain(null));
        $this->assertFalse($col->doesntContain([]));
        $this->assertFalse($col->doesntContain(0));
        $this->assertFalse($col->doesntContain(''));

        $col = new Collection([0]);
        $this->assertFalse($col->doesntContain(0));
        $this->assertFalse($col->doesntContain('0'));
        $this->assertFalse($col->doesntContain(false));
        $this->assertFalse($col->doesntContain(null));

        $this->assertFalse($col->doesntContain(function ($value) {
            return $value < 5;
        }));
        $this->assertTrue($col->doesntContain(function ($value) {
            return $value > 5;
        }));

        $col = new Collection([['v' => 1], ['v' => 3], ['v' => 5]]);

        $this->assertFalse($col->doesntContain('v', 1));
        $this->assertTrue($col->doesntContain('v', 2));

        $col = new Collection(['date', 'class', (object) ['foo' => 50]]);

        $this->assertFalse($col->doesntContain('date'));
        $this->assertFalse($col->doesntContain('class'));
        $this->assertTrue($col->doesntContain('foo'));

        $col = new Collection([
            null, 1, 2,
        ]);

        $this->assertFalse($col->doesntContain(function ($value) {
            return is_null($value);
        }));
    }

    public function testDot(): void
    {
        $col = Collection::make([
            'name' => 'Hyperf',
            'meta' => [
                'foo' => 'bar',
                'baz' => 'boom',
                'bam' => [
                    'boom' => 'bip',
                ],
            ],
        ])->dot();
        $this->assertSame([
            'name' => 'Hyperf',
            'meta.foo' => 'bar',
            'meta.baz' => 'boom',
            'meta.bam.boom' => 'bip',
        ], $col->all());

        $col = Collection::make([
            'foo' => [
                'bar',
                'baz',
                'baz' => 'boom',
            ],
        ])->dot();
        $this->assertSame([
            'foo.0' => 'bar',
            'foo.1' => 'baz',
            'foo.baz' => 'boom',
        ], $col->all());
    }

    public function testHasAny(): void
    {
        $col = new Collection(['id' => 1, 'first' => 'Hello', 'second' => 'World']);

        $this->assertTrue($col->hasAny('first'));
        $this->assertFalse($col->hasAny('third'));
        $this->assertTrue($col->hasAny(['first', 'second']));
        $this->assertTrue($col->hasAny(['first', 'fourth']));
        $this->assertFalse($col->hasAny(['third', 'fourth']));
        $this->assertFalse($col->hasAny('third', 'fourth'));
        $this->assertFalse($col->hasAny([]));
    }

    public function testIntersectAssocWithNull(): void
    {
        $collection = new Collection(['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red']);

        $this->assertEquals([], $collection->intersectAssoc(null)->all());
    }

    public function testIntersectAssocCollection(): void
    {
        $collectionOne = new Collection(['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red']);
        $collectionTwo = new Collection(['a' => 'green', 'b' => 'yellow', 'blue', 'red']);

        $this->assertEquals(['a' => 'green'], $collectionOne->intersectAssoc($collectionTwo)->all());
    }

    public function testMapWithKeys(): void
    {
        $data = new Collection([
            ['name' => 'Blastoise', 'type' => 'Water', 'idx' => 9],
            ['name' => 'Charmander', 'type' => 'Fire', 'idx' => 4],
            ['name' => 'Dragonair', 'type' => 'Dragon', 'idx' => 148],
        ]);
        $data = $data->mapWithKeys(function ($pokemon) {
            return [$pokemon['name'] => $pokemon['type']];
        });
        $this->assertEquals(
            ['Blastoise' => 'Water', 'Charmander' => 'Fire', 'Dragonair' => 'Dragon'],
            $data->all()
        );
    }

    public function testMapWithKeysIntegerKeys(): void
    {
        $data = new Collection([
            ['id' => 1, 'name' => 'A'],
            ['id' => 3, 'name' => 'B'],
            ['id' => 2, 'name' => 'C'],
        ]);
        $data = $data->mapWithKeys(function ($item) {
            return [$item['id'] => $item];
        });
        $this->assertSame(
            [1, 3, 2],
            $data->keys()->all()
        );
    }

    public function testMapWithKeysMultipleRows(): void
    {
        $data = new Collection([
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            ['id' => 3, 'name' => 'C'],
        ]);
        $data = $data->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name'], $item['name'] => $item['id']];
        });
        $this->assertSame(
            [
                1 => 'A',
                'A' => 1,
                2 => 'B',
                'B' => 2,
                3 => 'C',
                'C' => 3,
            ],
            $data->all()
        );
    }

    public function testMapWithKeysCallbackKey(): void
    {
        $data = new Collection([
            3 => ['id' => 1, 'name' => 'A'],
            5 => ['id' => 3, 'name' => 'B'],
            4 => ['id' => 2, 'name' => 'C'],
        ]);
        $data = $data->mapWithKeys(function ($item, $key) {
            return [$key => $item['id']];
        });
        $this->assertSame(
            [3, 5, 4],
            $data->keys()->all()
        );
    }

    public function testMergeRecursiveNull(): void
    {
        $col = new Collection(['name' => 'Hello']);
        $this->assertEquals(['name' => 'Hello'], $col->mergeRecursive(null)->all());
    }

    public function testMergeRecursiveArray(): void
    {
        $col = new Collection(['name' => 'Hello', 'id' => 1]);
        $this->assertEquals(['name' => 'Hello', 'id' => [1, 2]], $col->mergeRecursive(['id' => 2])->all());
    }

    public function testMergeRecursiveCollection(): void
    {
        $col = new Collection(['name' => 'Hello', 'id' => 1, 'meta' => ['tags' => ['a', 'b'], 'roles' => 'admin']]);
        $this->assertEquals(
            ['name' => 'Hello', 'id' => 1, 'meta' => ['tags' => ['a', 'b', 'c'], 'roles' => ['admin', 'editor']]],
            $col->mergeRecursive(new Collection(['meta' => ['tags' => ['c'], 'roles' => 'editor']]))->all()
        );
    }

    public function testForgetSingleKey(): void
    {
        $c = new Collection(['foo', 'bar']);
        $c = $c->forget(0)->all();
        $this->assertFalse(isset($c['foo']));
        $this->assertFalse(isset($c[0]));
        $this->assertTrue(isset($c[1]));
        $c = new Collection(['foo' => 'bar', 'baz' => 'qux']);
        $c = $c->forget('foo')->all();
        $this->assertFalse(isset($c['foo']));
        $this->assertTrue(isset($c['baz']));
    }

    public function testForgetArrayOfKeys(): void
    {
        $c = new Collection(['foo', 'bar', 'baz']);
        $c = $c->forget([0, 2])->all();
        $this->assertFalse(isset($c[0]));
        $this->assertFalse(isset($c[2]));
        $this->assertTrue(isset($c[1]));
        $c = new Collection(['name' => 'taylor', 'foo' => 'bar', 'baz' => 'qux']);
        $c = $c->forget(['foo', 'baz'])->all();
        $this->assertFalse(isset($c['foo']));
        $this->assertFalse(isset($c['baz']));
        $this->assertTrue(isset($c['name']));
    }

    public function testForgetCollectionOfKeys(): void
    {
        $c = new Collection(['foo', 'bar', 'baz']);
        $c = $c->forget(collect([0, 2]))->all();
        $this->assertFalse(isset($c[0]));
        $this->assertFalse(isset($c[2]));
        $this->assertTrue(isset($c[1]));

        $c = new Collection(['name' => 'taylor', 'foo' => 'bar', 'baz' => 'qux']);
        $c = $c->forget(collect(['foo', 'baz']))->all();
        $this->assertFalse(isset($c['foo']));
        $this->assertFalse(isset($c['baz']));
        $this->assertTrue(isset($c['name']));
    }

    public function testExcept(): void
    {
        $data = new Collection(['first' => 'Swoole', 'last' => 'Hyperf', 'email' => 'hyperf@gmail.com']);

        $this->assertEquals($data->all(), $data->except(null)->all());
        $this->assertEquals(['first' => 'Swoole'], $data->except(['last', 'email', 'missing'])->all());
        $this->assertEquals(['first' => 'Swoole'], $data->except('last', 'email', 'missing')->all());
        $this->assertEquals(['first' => 'Swoole'], $data->except(collect(['last', 'email', 'missing']))->all());

        $this->assertEquals(['first' => 'Swoole', 'email' => 'hyperf@gmail.com'], $data->except(['last'])->all());
        $this->assertEquals(['first' => 'Swoole', 'email' => 'hyperf@gmail.com'], $data->except('last')->all());
        $this->assertEquals(['first' => 'Swoole', 'email' => 'hyperf@gmail.com'], $data->except(collect(['last']))->all());
    }

    public function testRangeMethod(): void
    {
        $this->assertSame(
            [1, 2, 3, 4, 5],
            Collection::range(1, 5)->all()
        );

        $this->assertSame(
            [-2, -1, 0, 1, 2],
            Collection::range(-2, 2)->all()
        );

        $this->assertSame(
            [-4, -3, -2],
            Collection::range(-4, -2)->all()
        );

        $this->assertSame(
            [5, 4, 3, 2, 1],
            Collection::range(5, 1)->all()
        );

        $this->assertSame(
            [2, 1, 0, -1, -2],
            Collection::range(2, -2)->all()
        );

        $this->assertSame(
            [-2, -3, -4],
            Collection::range(-2, -4)->all()
        );
    }

    public function testReplaceNull(): void
    {
        $c = new Collection(['a', 'b', 'c']);
        $this->assertEquals(['a', 'b', 'c'], $c->replace(null)->all());
    }

    public function testReplaceArray(): void
    {
        $c = new Collection(['a', 'b', 'c']);
        $this->assertEquals(['a', 'd', 'e'], $c->replace([1 => 'd', 2 => 'e'])->all());

        $c = new Collection(['a', 'b', 'c']);
        $this->assertEquals(['a', 'd', 'e', 'f', 'g'], $c->replace([1 => 'd', 2 => 'e', 3 => 'f', 4 => 'g'])->all());

        $c = new Collection(['name' => 'amir', 'family' => 'otwell']);
        $this->assertEquals(['name' => 'taylor', 'family' => 'otwell', 'age' => 26], $c->replace(['name' => 'taylor', 'age' => 26])->all());
    }

    public function testReplaceCollection(): void
    {
        $c = new Collection(['a', 'b', 'c']);
        $this->assertEquals(
            ['a', 'd', 'e'],
            $c->replace(new Collection([1 => 'd', 2 => 'e']))->all()
        );

        $c = new Collection(['a', 'b', 'c']);
        $this->assertEquals(
            ['a', 'd', 'e', 'f', 'g'],
            $c->replace(new Collection([1 => 'd', 2 => 'e', 3 => 'f', 4 => 'g']))->all()
        );

        $c = new Collection(['name' => 'amir', 'family' => 'otwell']);
        $this->assertEquals(
            ['name' => 'taylor', 'family' => 'otwell', 'age' => 26],
            $c->replace(new Collection(['name' => 'taylor', 'age' => 26]))->all()
        );
    }

    public function testReplaceRecursiveNull(): void
    {
        $c = new Collection(['a', 'b', ['c', 'd']]);
        $this->assertEquals(['a', 'b', ['c', 'd']], $c->replaceRecursive(null)->all());
    }

    public function testReplaceRecursiveArray(): void
    {
        $c = new Collection(['a', 'b', ['c', 'd']]);
        $this->assertEquals(['z', 'b', ['c', 'e']], $c->replaceRecursive(['z', 2 => [1 => 'e']])->all());

        $c = new Collection(['a', 'b', ['c', 'd']]);
        $this->assertEquals(['z', 'b', ['c', 'e'], 'f'], $c->replaceRecursive(['z', 2 => [1 => 'e'], 'f'])->all());
    }

    public function testReplaceRecursiveCollection(): void
    {
        $c = new Collection(['a', 'b', ['c', 'd']]);
        $this->assertEquals(
            ['z', 'b', ['c', 'e']],
            $c->replaceRecursive(new Collection(['z', 2 => [1 => 'e']]))->all()
        );
    }

    public function testSkipMethod(): void
    {
        $data = new Collection([1, 2, 3, 4, 5, 6]);

        $this->assertSame([5, 6], $data->skip(4)->values()->all());
        $this->assertSame([], $data->skip(10)->values()->all());
    }

    public function testSliding(): void
    {
        $this->assertSame([], Collection::times(0)->sliding()->toArray());
        $this->assertSame([], Collection::times(1)->sliding()->toArray());
        $this->assertSame([[1, 2]], Collection::times(2)->sliding()->toArray());
        $this->assertSame(
            [[1, 2], [2, 3]],
            Collection::times(3)->sliding()->map->values()->toArray()
        );

        $this->assertSame([], Collection::times(1)->sliding(2, 3)->toArray());
        $this->assertSame([[1, 2]], Collection::times(2)->sliding(2, 3)->toArray());
        $this->assertSame([[1, 2]], Collection::times(3)->sliding(2, 3)->toArray());
        $this->assertSame([[1, 2]], Collection::times(4)->sliding(2, 3)->toArray());
        $this->assertSame(
            [[1, 2], [4, 5]],
            Collection::times(5)->sliding(2, 3)->map->values()->toArray()
        );

        $this->assertSame([], Collection::times(2)->sliding(3)->toArray());
        $this->assertSame([[1, 2, 3]], Collection::times(3)->sliding(3)->toArray());
        $this->assertSame(
            [[1, 2, 3], [2, 3, 4]],
            Collection::times(4)->sliding(3)->map->values()->toArray()
        );
        $this->assertSame(
            [[1, 2, 3], [2, 3, 4]],
            Collection::times(4)->sliding(3)->map->values()->toArray()
        );

        $this->assertSame([], Collection::times(2)->sliding(3, 2)->toArray());
        $this->assertSame([[1, 2, 3]], Collection::times(3)->sliding(3, 2)->toArray());
        $this->assertSame([[1, 2, 3]], Collection::times(4)->sliding(3, 2)->toArray());
        $this->assertSame(
            [[1, 2, 3], [3, 4, 5]],
            Collection::times(5)->sliding(3, 2)->map->values()->toArray()
        );
        $this->assertSame(
            [[1, 2, 3], [3, 4, 5]],
            Collection::times(6)->sliding(3, 2)->map->values()->toArray()
        );

        $chunks = Collection::times(3)->sliding();

        $this->assertSame([[0 => 1, 1 => 2], [1 => 2, 2 => 3]], $chunks->toArray());

        $this->assertInstanceOf(Collection::class, $chunks);
        $this->assertInstanceOf(Collection::class, $chunks->first());
        $this->assertInstanceOf(Collection::class, $chunks->skip(1)->first());
    }

    public function testSortDesc(): void
    {
        $data = (new Collection([5, 3, 1, 2, 4]))->sortDesc();
        $this->assertEquals([5, 4, 3, 2, 1], $data->values()->all());

        $data = (new Collection([-1, -3, -2, -4, -5, 0, 5, 3, 1, 2, 4]))->sortDesc();
        $this->assertEquals([5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5], $data->values()->all());

        $data = (new Collection(['bar-1', 'foo', 'bar-10']))->sortDesc();
        $this->assertEquals(['foo', 'bar-10', 'bar-1'], $data->values()->all());

        $data = (new Collection(['T2', 'T1', 'T10']))->sortDesc();
        $this->assertEquals(['T2', 'T10', 'T1'], $data->values()->all());

        $data = (new Collection(['T2', 'T1', 'T10']))->sortDesc(SORT_NATURAL);
        $this->assertEquals(['T10', 'T2', 'T1'], $data->values()->all());
    }

    public function testSortKeysUsing(): void
    {
        $data = new Collection(['B' => 'dayle', 'a' => 'taylor']);

        $this->assertSame(['a' => 'taylor', 'B' => 'dayle'], $data->sortKeysUsing('strnatcasecmp')->all());
    }

    public function testSplitIn(): void
    {
        $data = new Collection([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $data = $data->splitIn(3);

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertInstanceOf(Collection::class, $data->first());
        $this->assertCount(3, $data);
        $this->assertEquals([1, 2, 3, 4], $data->get(0)->values()->toArray());
        $this->assertEquals([5, 6, 7, 8], $data->get(1)->values()->toArray());
        $this->assertEquals([9, 10], $data->get(2)->values()->toArray());
    }

    public function testIsset()
    {
        $data = [null, 1];
        $c = new Collection($data);
        $this->assertFalse(isset($data[0]));
        $this->assertFalse(isset($c[0]));
        $this->assertTrue(isset($data[1]));
        $this->assertTrue(isset($c[1]));
    }

    public function testUnshiftWithOneItem()
    {
        $expected = [
            0 => 'Jonny from Laroe',
            1 => ['who' => 'Jonny', 'preposition' => 'from', 'where' => 'Laroe'],
            2 => ['a', 'b', 'c'],
            3 => 4,
            4 => 5,
            5 => 6,
        ];

        $data = new Collection([4, 5, 6]);
        $data->unshift(['a', 'b', 'c']);
        $data->unshift(['who' => 'Jonny', 'preposition' => 'from', 'where' => 'Laroe']);
        $actual = $data->unshift('Jonny from Laroe')->toArray();

        $this->assertSame($expected, $actual);
    }

    public function testUnshiftWithMultipleItems()
    {
        $expected = [
            0 => 'a',
            1 => 'b',
            2 => 'c',
            3 => 'Jonny',
            4 => 'from',
            5 => 'Laroe',
            6 => 'Jonny',
            7 => 'from',
            8 => 'Laroe',
            9 => 4,
            10 => 5,
            11 => 6,
        ];

        $data = new Collection([4, 5, 6]);
        $data->unshift('Jonny', 'from', 'Laroe');
        $data->unshift(...[11 => 'Jonny', 12 => 'from', 13 => 'Laroe']);
        $data->unshift(...collect(['a', 'b', 'c']));
        $actual = $data->unshift(...[])->toArray();

        $this->assertSame($expected, $actual);
    }

    public function testWhen()
    {
        $c = (new Collection([]))
            ->when(true, fn (Collection $collection) => $collection->push(1))
            ->when(false, fn (Collection $collection) => $collection->push(2))
            ->when(null, fn (Collection $collection) => $collection->push(3))
            ->when('', fn (Collection $collection) => $collection->push(4))
            ->when([], fn (Collection $collection) => $collection->push(5))
            ->when(0, fn (Collection $collection) => $collection->push(6));

        $this->assertSame([1], $c->all());
    }

    public function testWhenWithValueForCallback(): void
    {
        $callback = fn (Collection $collection, string $value) => $collection->push($value);

        $c = (new Collection([]))
            ->when('foo', $callback)
            ->when('', $callback);

        $this->assertSame(['foo'], $c->all());
    }

    public function testWhenValueOfClosure(): void
    {
        $callback = fn (Collection $collection, $value) => $collection->push($value);

        $c = (new Collection([]))->when(fn () => 'foo', $callback)->when(fn () => '', $callback);
        $this->assertSame(['foo'], $c->all());

        $c = (new Collection([1, 2]))->when(
            fn (Collection $collection) => $collection->shift(),
            $callback
        );
        $this->assertSame([2, 1], $c->all());
    }

    public function testWhenCallbackWithDefault(): void
    {
        $callback = fn (Collection $collection, $value) => $collection;
        $default = fn (Collection $collection, $value) => $collection->push($value);

        $c = (new Collection([]))->when('foo', $callback, $default)->when('', $callback, $default);
        $this->assertSame([''], $c->all());
    }

    public function testUnless()
    {
        $c = (new Collection([]))
            ->unless(true, fn (Collection $collection) => $collection->push(1))
            ->unless(false, fn (Collection $collection) => $collection->push(2))
            ->unless(null, fn (Collection $collection) => $collection->push(3))
            ->unless('', fn (Collection $collection) => $collection->push(4))
            ->unless([], fn (Collection $collection) => $collection->push(5))
            ->unless(0, fn (Collection $collection) => $collection->push(6));

        $this->assertSame([2, 3, 4, 5, 6], $c->all());
    }

    public function testUnlessWithValueForCallback(): void
    {
        $callback = fn (Collection $collection, string $value) => $collection->push($value);

        $c = (new Collection([]))
            ->unless('foo', $callback)
            ->unless('', $callback);

        $this->assertSame([''], $c->all());
    }

    public function testUnlessValueOfClosure(): void
    {
        $callback = fn (Collection $collection, $value) => $collection->push($value);

        $c = (new Collection([]))->unless(fn () => 'foo', $callback)->unless(fn () => '', $callback);
        $this->assertSame([''], $c->all());

        $c = (new Collection([1, 2]))->unless(
            fn (Collection $collection) => $collection->shift(),
            $callback
        );
        $this->assertSame([2], $c->all());
    }

    public function testUnlessCallbackWithDefault(): void
    {
        $callback = fn (Collection $collection, $value) => $collection;
        $default = fn (Collection $collection, $value) => $collection->push($value);

        $c = (new Collection([]))->unless('foo', $callback, $default)->unless('', $callback, $default);
        $this->assertSame(['foo'], $c->all());
    }
}
