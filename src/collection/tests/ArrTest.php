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

use Hyperf\Collection\Arr;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Resolver\ResolverDispatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ArrTest extends TestCase
{
    public function testArrGet(): void
    {
        $data = ['id' => 1, 'name' => 'Hyperf'];
        $this->assertSame(1, Arr::get($data, 'id'));
        $this->assertSame('Hyperf', Arr::get($data, 'name'));
        $this->assertSame($data, Arr::get($data));
        $this->assertNull(Arr::get($data, 'gender'));
        $this->assertSame(1, Arr::get($data, 'gender', 1));

        $data = [1, 2, 3, 4];
        $this->assertSame(1, Arr::get($data, 0));
        $this->assertSame(5, Arr::get($data, 4, 5));
        $this->assertNull(Arr::get($data, 5));

        $object = new stdClass();
        $object->id = 1;
        $this->assertNull(Arr::get($object, 'id'));
    }

    public function testArrSet(): void
    {
        $data = ['id' => 1, 'name' => 'Hyperf'];
        Arr::set($data, 'id', 2);
        $this->assertSame(['id' => 2, 'name' => 'Hyperf'], $data);
        Arr::set($data, 'gender', 2);
        $this->assertSame(['id' => 2, 'name' => 'Hyperf', 'gender' => 2], $data);
        Arr::set($data, 'book.0', 'Hello Hyperf');
        $this->assertSame(['id' => 2, 'name' => 'Hyperf', 'gender' => 2, 'book' => ['Hello Hyperf']], $data);
        Arr::set($data, 'rel.id', 2);
        $this->assertSame(['id' => 2, 'name' => 'Hyperf', 'gender' => 2, 'book' => ['Hello Hyperf'], 'rel' => ['id' => 2]], $data);
        Arr::set($data, null, [1, 2, 3]);
        $this->assertSame([1, 2, 3], $data);

        $data = [1, 2, 3, 4];
        Arr::set($data, 0, 2);
        $this->assertSame([2, 2, 3, 4], $data);
        Arr::set($data, 4, 2);
        $this->assertSame([2, 2, 3, 4, 2], $data);
    }

    public function testArrMerge(): void
    {
        $this->assertSame([1, 2, 3, 4], Arr::merge([1, 2, 3], [2, 3, 4]));
        $this->assertSame([1, 2, 3, 2, 3, 4], Arr::merge([1, 2, 3], [2, 3, 4], false));
        $this->assertSame([1, 2, [1, 2], 3, 4], Arr::merge([1, 2, [1, 2]], [[1, 2], 3, 4]));
        $this->assertSame([1, 2, [1, 2], [1, 2], 3, 4], Arr::merge([1, 2, [1, 2]], [[1, 2], 3, 4], false));
        $this->assertSame([1, 2, 3, '2', 4], Arr::merge([1, 2, 3], ['2', 3, 4]));

        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], Arr::merge(['id' => 1], ['name' => 'Hyperf']));
        $this->assertSame(['id' => 1, 'name' => 'Hyperf', 'gender' => 1], Arr::merge(['id' => 1, 'name' => 'Swoole'], ['name' => 'Hyperf', 'gender' => 1]));
        $this->assertSame(['id' => 1, 'ids' => [1, 2, 3], 'name' => 'Hyperf'], Arr::merge(['id' => 1, 'ids' => [1, 2]], ['name' => 'Hyperf', 'ids' => [1, 2, 3]]));
        $this->assertSame(['id' => 1, 'ids' => [1, 2, 1, 2, 3], 'name' => 'Hyperf'], Arr::merge(['id' => 1, 'ids' => [1, 2]], ['name' => 'Hyperf', 'ids' => [1, 2, 3]], false));

        $this->assertSame(['id' => 1, 'name' => ['Hyperf']], Arr::merge(['id' => 2], ['id' => 1, 'name' => ['Hyperf']]));
        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], Arr::merge([], ['id' => 1, 'name' => 'Hyperf']));
        $this->assertSame([1, 2, 3], Arr::merge([], [1, 2, 3]));
        $this->assertSame([1, 2, 3], Arr::merge([], [1, 2, 2, 3]));
        $this->assertSame([1, 2, [1, 2, 3]], Arr::merge([], [1, 2, 2, [1, 2, 3], [1, 2, 3]]));
        $this->assertSame([1, 2, [1, 2, 3], [1, 2, 3, 4]], Arr::merge([], [1, 2, 2, [1, 2, 3], [1, 2, 3, 4]]));
        $this->assertSame([1, 2, 3], Arr::merge([1, 2], ['id' => 3]));
        $this->assertSame([1, 2, 'id' => 3], Arr::merge([], [1, 2, 'id' => 3]));

        $array1 = [
            'logger' => [
                'default' => [
                    'handler' => [
                        'class' => StreamHandler::class,
                        'constructor' => [
                            'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                            'level' => Level::Debug,
                        ],
                    ],
                ],
            ],
            'scan' => [
                'paths' => [
                    BASE_PATH . '/app',
                ],
                'ignore_annotations' => [
                    'mixin',
                ],
                'class_map' => [
                    Coroutine::class => BASE_PATH . '/app/Kernel/ClassMap/Coroutine.php',
                ],
            ],
        ];

        $array2 = [
            'logger' => [
                'default' => [
                    'handler' => [
                        'class' => StreamHandler::class,
                        'constructor' => [
                            'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                            'level' => Level::Info,
                        ],
                    ],
                ],
            ],
            'scan' => [
                'ignore_annotations' => [
                    'mixin',
                    'author',
                ],
                'class_map' => [
                    Coroutine::class => BASE_PATH . '/app/Kernel/ClassMap/Coroutine.php',
                    ResolverDispatcher::class => BASE_PATH . '/vendor/hyperf/di/class_map/Resolver/ResolverDispatcher.php',
                ],
            ],
        ];

        $result = Arr::merge($array1, $array2);
        $array1['logger']['default']['handler']['constructor']['level'] = Level::Info;
        $array1['scan']['class_map'][ResolverDispatcher::class] = BASE_PATH . '/vendor/hyperf/di/class_map/Resolver/ResolverDispatcher.php';
        $array1['scan']['ignore_annotations'][] = 'author';

        $this->assertSame($array1, $result);

        $result = Arr::merge($result, $array2);
        $this->assertSame($array1, $result);
    }

    public function testArrayForget(): void
    {
        $data = [1, 2];
        Arr::forget($data, [1]);
        $this->assertSame([1], $data);

        $data = ['id' => 1, 'name' => 'Hyperf'];
        Arr::forget($data, ['gender']);
        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], $data);
        Arr::forget($data, ['id']);
        $this->assertSame(['name' => 'Hyperf'], $data);

        $data = ['id' => 1, 'name' => 'Hyperf', 'data' => ['id' => 2], 'data.name' => 'Swoole'];
        Arr::forget($data, ['data.gender']);
        $this->assertSame(['id' => 1, 'name' => 'Hyperf', 'data' => ['id' => 2], 'data.name' => 'Swoole'], $data);
        Arr::forget($data, ['data.name']);
        $this->assertSame(['id' => 1, 'name' => 'Hyperf', 'data' => ['id' => 2]], $data);
        Arr::forget($data, ['data.id']);
        $this->assertSame(['id' => 1, 'name' => 'Hyperf', 'data' => []], $data);

        $data = ['data' => ['data' => ['id' => 1, 'name' => 'Hyperf']]];
        Arr::forget($data, ['data.data.id']);
        $this->assertSame(['data' => ['data' => ['name' => 'Hyperf']]], $data);

        $data = [1, 2];
        Arr::forget($data, [2]);
        $this->assertSame([1, 2], $data);
    }

    public function testArrMacro(): void
    {
        Arr::macro('foo', static function () {
            return 'foo';
        });

        $this->assertTrue(Arr::hasMacro('foo'));
        $this->assertFalse(Arr::hasMacro('bar'));
    }

    public function testShuffle(): void
    {
        $source = range('a', 'z'); // alphabetic keys to ensure values are returned

        $sameElements = true;
        $dontMatch = false;

        // Attempt 5x times to prevent random failures
        for ($i = 0; $i < 5; ++$i) {
            $shuffled = Arr::shuffle($source);

            $dontMatch = $dontMatch || $source !== $shuffled;
            $sameElements = $sameElements && $source === array_values(Arr::sort($shuffled));
        }

        $this->assertTrue($sameElements, 'Shuffled array should always have the same elements.');
        $this->assertTrue($dontMatch, 'Shuffled array should not have the same order.');
    }

    public function testShuffleWithSeed(): void
    {
        $this->assertSame(
            Arr::shuffle(range(0, 100, 10), 1234),
            Arr::shuffle(range(0, 100, 10), 1234)
        );

        $this->assertNotSame(
            Arr::shuffle(range(0, 100, 10)),
            Arr::shuffle(range(0, 100, 10))
        );

        $this->assertNotSame(
            range(0, 100, 10),
            Arr::shuffle(range(0, 100, 10), 1234)
        );
    }

    public function testEmptyShuffle(): void
    {
        $this->assertEquals([], Arr::shuffle([]));
    }

    public function testMapWithKeys(): void
    {
        $data = [
            ['name' => 'Blastoise', 'type' => 'Water', 'idx' => 9],
            ['name' => 'Charmander', 'type' => 'Fire', 'idx' => 4],
            ['name' => 'Dragonair', 'type' => 'Dragon', 'idx' => 148],
        ];

        $data = Arr::mapWithKeys($data, static function ($pokemon) {
            return [$pokemon['name'] => $pokemon['type']];
        });

        $this->assertEquals(
            ['Blastoise' => 'Water', 'Charmander' => 'Fire', 'Dragonair' => 'Dragon'],
            $data
        );
    }

    public function testHasMethod(): void
    {
        $array = ['name' => 'Taylor', 'age' => '', 'city' => null];
        $this->assertTrue(Arr::has($array, 'name'));
        $this->assertTrue(Arr::has($array, ['name', 'age']));
        $this->assertFalse(Arr::has($array, ['name', 'age', 'gender']));
        $this->assertFalse(Arr::has($array, 1));
    }

    public function testHasAnyMethod(): void
    {
        $array = ['name' => 'Taylor', 'age' => '', 'city' => null];
        $this->assertTrue(Arr::hasAny($array, 'name'));
        $this->assertTrue(Arr::hasAny($array, 'age'));
        $this->assertTrue(Arr::hasAny($array, 'city'));
        $this->assertFalse(Arr::hasAny($array, 'foo'));
        $this->assertTrue(Arr::hasAny($array, 'name'));
        $this->assertTrue(Arr::hasAny($array, ['name', 'email']));

        $array = ['name' => 'Taylor', 'email' => 'foo'];
        $this->assertTrue(Arr::hasAny($array, 'name'));
        $this->assertFalse(Arr::hasAny($array, 'surname'));
        $this->assertFalse(Arr::hasAny($array, ['surname', 'password']));

        $array = ['foo' => ['bar' => null, 'baz' => '']];
        $this->assertTrue(Arr::hasAny($array, 'foo.bar'));
        $this->assertTrue(Arr::hasAny($array, 'foo.baz'));
        $this->assertFalse(Arr::hasAny($array, 'foo.bax'));
        $this->assertTrue(Arr::hasAny($array, ['foo.bax', 'foo.baz']));
    }

    public function testIsAssoc(): void
    {
        $this->assertTrue(Arr::isAssoc(['a' => 'a', 0 => 'b']));
        $this->assertTrue(Arr::isAssoc([1 => 'a', 0 => 'b']));
        $this->assertTrue(Arr::isAssoc([1 => 'a', 2 => 'b']));
        $this->assertFalse(Arr::isAssoc([0 => 'a', 1 => 'b']));
        $this->assertFalse(Arr::isAssoc(['a', 'b']));

        $this->assertFalse(Arr::isAssoc([]));
        $this->assertFalse(Arr::isAssoc([1, 2, 3]));
        $this->assertFalse(Arr::isAssoc(['foo', 2, 3]));
        $this->assertFalse(Arr::isAssoc([0 => 'foo', 'bar']));

        $this->assertTrue(Arr::isAssoc([1 => 'foo', 'bar']));
        $this->assertTrue(Arr::isAssoc([0 => 'foo', 'bar' => 'baz']));
        $this->assertTrue(Arr::isAssoc([0 => 'foo', 2 => 'bar']));
        $this->assertTrue(Arr::isAssoc(['foo' => 'bar', 'baz' => 'qux']));
    }

    public function testIsList(): void
    {
        $this->assertTrue(Arr::isList([]));
        $this->assertTrue(Arr::isList([1, 2, 3]));
        $this->assertTrue(Arr::isList(['foo', 2, 3]));
        $this->assertTrue(Arr::isList(['foo', 'bar']));
        $this->assertTrue(Arr::isList([0 => 'foo', 'bar']));
        $this->assertTrue(Arr::isList([0 => 'foo', 1 => 'bar']));

        $this->assertFalse(Arr::isList([1 => 'foo', 'bar']));
        $this->assertFalse(Arr::isList([1 => 'foo', 0 => 'bar']));
        $this->assertFalse(Arr::isList([0 => 'foo', 'bar' => 'baz']));
        $this->assertFalse(Arr::isList([0 => 'foo', 2 => 'bar']));
        $this->assertFalse(Arr::isList(['foo' => 'bar', 'baz' => 'qux']));
    }

    public function testArrayRemove(): void
    {
        $data = [1 => 'a', 2 => 'b', 3 => 'c'];
        $this->assertSame(['b'], Arr::remove($data, 'a', 'c'));

        $data = [1, 2, 3, 4];
        $this->assertSame([3, 4], Arr::remove($data, 1, 2));

        $data = [1, 2, 3, 4];
        $this->assertSame($data, Arr::remove($data, 5));

        $data = [3, 4, 3, 3];
        $this->assertSame([4], Arr::remove($data, 3));

        $data = [1 => 'a', 2 => 'b', 3 => 'a'];
        $this->assertSame(['b'], Arr::remove($data, 'a'));
    }

    public function testArrayRemoveKeepKey(): void
    {
        $data = [1 => 'a', 2 => 'b', 3 => 'c'];
        $this->assertSame([2 => 'b'], Arr::removeKeepKey($data, 'a', 'c'));

        $data = [1, 2, 3, 4];
        $this->assertSame([2 => 3, 3 => 4], Arr::removeKeepKey($data, 1, 2));

        $data = [1, 2, 3, 4];
        $this->assertSame($data, Arr::removeKeepKey($data, 5));

        $data = [3, 4, 3, 3];
        $this->assertSame([1 => 4], Arr::removeKeepKey($data, 3));

        $data = [1 => 'a', 2 => 'b', 3 => 'a'];
        $this->assertSame([2 => 'b'], Arr::removeKeepKey($data, 'a'));
    }

    public function testArrayUndot(): void
    {
        $data = ['user' => ['name' => 'Hyperf', 'age' => 18]];
        $this->assertSame($dotData = Arr::dot($data), [
            'user.name' => 'Hyperf',
            'user.age' => 18,
        ]);

        $this->assertSame($data, Arr::undot($dotData));

        $data = ['user' => [['name' => 'Hyperf', 'age' => 18], ['name' => 'test', 'age' => 21]]];
        $this->assertSame($dotData = Arr::dot($data), [
            'user.0.name' => 'Hyperf',
            'user.0.age' => 18,
            'user.1.name' => 'test',
            'user.1.age' => 21,
        ]);

        $this->assertSame($data, Arr::undot($dotData));
    }
}
