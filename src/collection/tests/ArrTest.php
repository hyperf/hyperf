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

use ArrayObject;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
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

    public function testShuffleAssoc(): void
    {
        $source = [];
        $value = 'a';
        for ($i = 0; $i < 1000; ++$i) {
            $source[$value++] = $i;
        }

        $shuffled = Arr::shuffleAssoc($source);

        $this->assertNotSame($source, $shuffled);
        $this->assertSameSize($source, $shuffled);
        $this->assertSameSize($source, array_intersect_assoc($source, $shuffled));

        $source = [];
        $value = 'a';
        for ($i = 0; $i < 1000; ++$i) {
            $source[] = $value++;
        }

        $shuffled = Arr::shuffleAssoc($source);

        $this->assertNotSame($source, $shuffled);
        $this->assertSameSize($source, $shuffled);
        $this->assertSameSize($source, array_intersect_assoc($source, $shuffled));

        $source = [];
        for ($i = 0; $i < 1000; ++$i) {
            $source[] = $i;
        }

        $shuffled = Arr::shuffleAssoc($source);

        $this->assertNotSame($source, $shuffled);
        $this->assertSameSize($source, $shuffled);
        $this->assertSameSize($source, array_intersect_assoc($source, $shuffled));
    }

    public function testShuffleAssocWithSeed(): void
    {
        $source = [];
        $value = 'a';
        for ($i = 0; $i < 1000; ++$i) {
            $source[$value++] = $i;
        }

        $this->assertSame(
            Arr::shuffleAssoc($source, 1234),
            Arr::shuffleAssoc($source, 1234)
        );
        $this->assertNotSame(
            Arr::shuffleAssoc($source),
            Arr::shuffleAssoc($source)
        );
        $this->assertNotSame(
            $source,
            Arr::shuffleAssoc($source, 1234)
        );

        $source = [];
        $value = 'a';
        for ($i = 0; $i < 1000; ++$i) {
            $source[] = $value++;
        }

        $this->assertSame(
            Arr::shuffleAssoc($source, 1234),
            Arr::shuffleAssoc($source, 1234)
        );
        $this->assertNotSame(
            Arr::shuffleAssoc($source),
            Arr::shuffleAssoc($source)
        );
        $this->assertNotSame(
            $source,
            Arr::shuffleAssoc($source, 1234)
        );

        $source = [];
        for ($i = 0; $i < 1000; ++$i) {
            $source[] = $i;
        }

        $this->assertSame(
            Arr::shuffleAssoc($source, 1234),
            Arr::shuffleAssoc($source, 1234)
        );
        $this->assertNotSame(
            Arr::shuffleAssoc($source),
            Arr::shuffleAssoc($source)
        );
        $this->assertNotSame(
            $source,
            Arr::shuffleAssoc($source, 1234)
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

    public function testToCssClasses(): void
    {
        $this->assertSame('foo bar', Arr::toCssClasses(['foo', 'bar']));
        $this->assertSame('foo bar', Arr::toCssClasses(['foo' => true, 'bar' => true]));
        $this->assertSame('foo', Arr::toCssClasses(['foo' => true, 'bar' => false]));
        $this->assertSame('foo', Arr::toCssClasses(['foo', 'bar' => false]));
        $this->assertSame('foo bar', Arr::toCssClasses(['foo' => true, 'bar']));
        $this->assertSame('foo', Arr::toCssClasses(['foo' => true, 'bar' => null]));
        $this->assertSame('foo', Arr::toCssClasses(['foo' => true, 'bar' => '']));
        $this->assertSame('foo', Arr::toCssClasses(['foo' => true, 'bar' => 0]));
        $this->assertSame('foo', Arr::toCssClasses(['foo' => true, 'bar' => '0']));
        $this->assertSame('foo', Arr::toCssClasses(['foo' => true, 'bar' => []]));
        $this->assertSame('foo bar', Arr::toCssClasses(['foo' => true, 'bar' => 'baz']));
        $this->assertSame('foo bar', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => false]));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true]));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true, 'qux' => false]));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true, 'qux' => null]));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true, 'qux' => '']));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true, 'qux' => 0]));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true, 'qux' => '0']));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true, 'qux' => []]));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true, 'qux' => '0', 'quux' => '0']));
        $this->assertSame('foo bar baz', Arr::toCssClasses(['foo' => true, 'bar' => 'baz', 'baz' => true, 'qux' => '0', 'quux' => '0', 'quuz' => '0']));
    }

    public function testToCssStyles(): void
    {
        $this->assertSame('color: red; background-color: blue;', Arr::toCssStyles([
            'color: red' => true,
            'background-color: blue' => true,
        ]));
        $this->assertSame('color: red;', Arr::toCssStyles([
            'color: red' => true,
            'background-color: blue' => false,
        ]));
        $stylesArray = [
            'margin: 0 auto' => true,
            'padding: 10px' => true,
            'display: block' => true,
            'color' => false,
        ];
        $expectedCss = 'margin: 0 auto; padding: 10px; display: block;';
        $this->assertSame($expectedCss, Arr::toCssStyles($stylesArray));
        $numericArray = [
            'font-size: 12px' => true,
            'color: #000000' => true,
            0 => 'width: 100%',
            1 => 'height: 100%',
        ];
        $expectedNumericCss = 'font-size: 12px; color: #000000; width: 100%; height: 100%;';
        $this->assertSame($expectedNumericCss, Arr::toCssStyles($numericArray));
        $expectedCss = 'margin: 0 auto; padding: 10px; display: block; font-size: 12px; color: #000000; width: 100%; height: 100%;';
        $this->assertSame($expectedCss, Arr::toCssStyles(array_merge($stylesArray, $numericArray)));
    }

    public function testJoin(): void
    {
        $this->assertSame('', Arr::join([], ','), 'Join with empty array should return an empty string.');
        $this->assertSame('item1', Arr::join(['item1'], ','), 'Join with single element array should return the element itself.');
        $array = ['item1', 'item2', 'item3'];
        $this->assertSame('item1,item2,item3', Arr::join($array, ','), 'Join with multiple elements and default glue.');
        $array = ['item1', 'item2', 'item3'];
        $this->assertSame('item1|item2|item3', Arr::join($array, '|'), 'Join with multiple elements and custom glue.');
        $array = ['item1', 'item2', 'item3'];
        $this->assertSame('item1,item2 and item3', Arr::join($array, ',', ' and '), 'Join with multiple elements, default glue, and custom final glue.');
        $array = ['item1'];
        $this->assertSame('item1', Arr::join($array, ',', ' and '), 'Join with single element and custom final glue should ignore the final glue.');
        $array = ['', ''];
        $this->assertSame(',', Arr::join($array, ','), 'Join with array of empty strings should return an empty string.');
        $array = [1, 'item2', 3.0];
        $this->assertSame('1,item2,3', Arr::join($array, ','), 'Join should handle different data types correctly.');
    }

    public function testKeyBy(): void
    {
        $array = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
        ];
        $expected = [
            1 => ['id' => 1, 'name' => 'Alice'],
            2 => ['id' => 2, 'name' => 'Bob'],
            3 => ['id' => 3, 'name' => 'Charlie'],
        ];
        $result = Arr::keyBy($array, 'id');
        $this->assertEquals($expected, $result);

        $array = [
            ['product_id' => 101, 'name' => 'Apple'],
            ['product_id' => 102, 'name' => 'Banana'],
            ['product_id' => 103, 'name' => 'Cherry'],
        ];
        $expected = [
            101 => ['product_id' => 101, 'name' => 'Apple'],
            102 => ['product_id' => 102, 'name' => 'Banana'],
            103 => ['product_id' => 103, 'name' => 'Cherry'],
        ];
        $result = Arr::keyBy($array, function ($item) {
            return $item['product_id'];
        });
        $this->assertEquals($expected, $result);
        $array = [
            ['employee_id' => 1001, 'first_name' => 'John', 'last_name' => 'Doe'],
            ['employee_id' => 1002, 'first_name' => 'Jane', 'last_name' => 'Smith'],
            ['employee_id' => 1003, 'first_name' => 'Jim', 'last_name' => 'Beam'],
        ];
        $expected = [
            1001 => ['employee_id' => 1001, 'first_name' => 'John', 'last_name' => 'Doe'],
            1002 => ['employee_id' => 1002, 'first_name' => 'Jane', 'last_name' => 'Smith'],
            1003 => ['employee_id' => 1003, 'first_name' => 'Jim', 'last_name' => 'Beam'],
        ];
        $result = Arr::keyBy($array, 'employee_id');
        $this->assertEquals($expected, $result);
    }

    public function testPrependKeysWith(): void
    {
        $array1 = ['name' => 'Alice', 'age' => 25];
        $expected1 = ['user_name' => 'Alice', 'user_age' => 25];
        $this->assertEquals($expected1, Arr::prependKeysWith($array1, 'user_'));

        $array2 = [];
        $expected2 = [];
        $this->assertEquals($expected2, Arr::prependKeysWith($array2, 'user_'));

        $array3 = ['name' => 'Bob'];
        $expected3 = ['name' => 'Bob'];
        $this->assertEquals($expected3, Arr::prependKeysWith($array3, ''));

        $array4 = ['details' => ['name' => 'Charlie']];
        $expected4 = ['user_details' => ['name' => 'Charlie']];
        $this->assertEquals($expected4, Arr::prependKeysWith($array4, 'user_'));

        $object = new stdClass();
        $object->name = 'David';
        $array5 = ['person' => $object];
        $expected5 = ['user_person' => $object];
        $this->assertEquals($expected5, Arr::prependKeysWith($array5, 'user_'));
    }

    public function testSelect()
    {
        $array = [
            [
                'name' => 'Taylor',
                'role' => 'Developer',
                'age' => 1,
            ],
            [
                'name' => 'Abigail',
                'role' => 'Infrastructure',
                'age' => 2,
            ],
        ];

        $this->assertEquals([
            [
                'name' => 'Taylor',
                'age' => 1,
            ],
            [
                'name' => 'Abigail',
                'age' => 2,
            ],
        ], Arr::select($array, ['name', 'age']));

        $this->assertEquals([
            [
                'name' => 'Taylor',
            ],
            [
                'name' => 'Abigail',
            ],
        ], Arr::select($array, 'name'));
    }

    public function testMapSpread(): void
    {
        $c = [[1, 'a'], [2, 'b']];

        $result = Arr::mapSpread($c, static function ($number, $character) {
            return "{$number}-{$character}";
        });
        $this->assertEquals(['1-a', '2-b'], $result);

        $result = Arr::mapSpread($c, static function ($number, $character, $key) {
            return "{$number}-{$character}-{$key}";
        });
        $this->assertEquals(['1-a-0', '2-b-1'], $result);
    }

    public function testSortDesc(): void
    {
        $unsorted = [
            ['name' => 'Chair'],
            ['name' => 'Desk'],
        ];

        $expected = [
            ['name' => 'Desk'],
            ['name' => 'Chair'],
        ];

        $sorted = array_values(Arr::sortDesc($unsorted));
        $this->assertEquals($expected, $sorted);

        // sort with closure
        $sortedWithClosure = array_values(Arr::sortDesc($unsorted, function ($value) {
            return $value['name'];
        }));
        $this->assertEquals($expected, $sortedWithClosure);

        // sort with dot notation
        $sortedWithDotNotation = array_values(Arr::sortDesc($unsorted, 'name'));
        $this->assertEquals($expected, $sortedWithDotNotation);
    }

    public function testSortRecursive(): void
    {
        $array = [
            'users' => [
                [
                    // should sort associative arrays by keys
                    'name' => 'joe',
                    'mail' => 'joe@example.com',
                    // should sort deeply nested arrays
                    'numbers' => [2, 1, 0],
                ],
                [
                    'name' => 'jane',
                    'age' => 25,
                ],
            ],
            'repositories' => [
                // should use weird `sort()` behavior on arrays of arrays
                ['id' => 1],
                ['id' => 0],
            ],
            // should sort non-associative arrays by value
            20 => [2, 1, 0],
            30 => [
                // should sort non-incrementing numerical keys by keys
                2 => 'a',
                1 => 'b',
                0 => 'c',
            ],
        ];

        $expect = [
            20 => [0, 1, 2],
            30 => [
                0 => 'c',
                1 => 'b',
                2 => 'a',
            ],
            'repositories' => [
                ['id' => 0],
                ['id' => 1],
            ],
            'users' => [
                [
                    'age' => 25,
                    'name' => 'jane',
                ],
                [
                    'mail' => 'joe@example.com',
                    'name' => 'joe',
                    'numbers' => [0, 1, 2],
                ],
            ],
        ];

        $this->assertEquals($expect, Arr::sortRecursive($array));
    }

    public function testDivide(): void
    {
        // Test dividing an empty array
        [$keys, $values] = Arr::divide([]);
        $this->assertEquals([], $keys);
        $this->assertEquals([], $values);

        // Test dividing an array with a single key-value pair
        [$keys, $values] = Arr::divide(['name' => 'Desk']);
        $this->assertEquals(['name'], $keys);
        $this->assertEquals(['Desk'], $values);

        // Test dividing an array with multiple key-value pairs
        [$keys, $values] = Arr::divide(['name' => 'Desk', 'price' => 100, 'available' => true]);
        $this->assertEquals(['name', 'price', 'available'], $keys);
        $this->assertEquals(['Desk', 100, true], $values);

        // Test dividing an array with numeric keys
        [$keys, $values] = Arr::divide([0 => 'first', 1 => 'second']);
        $this->assertEquals([0, 1], $keys);
        $this->assertEquals(['first', 'second'], $values);

        // Test dividing an array with null key
        [$keys, $values] = Arr::divide([null => 'Null', 1 => 'one']);
        $this->assertEquals([null, 1], $keys);
        $this->assertEquals(['Null', 'one'], $values);

        // Test dividing an array where the keys are arrays
        [$keys, $values] = Arr::divide([['one' => 1, 2 => 'second'], 1 => 'one']);
        $this->assertEquals([0, 1], $keys);
        $this->assertEquals([['one' => 1, 2 => 'second'], 'one'], $values);

        // Test dividing an array where the values are arrays
        [$keys, $values] = Arr::divide([null => ['one' => 1, 2 => 'second'], 1 => 'one']);
        $this->assertEquals([null, 1], $keys);
        $this->assertEquals([['one' => 1, 2 => 'second'], 'one'], $values);
    }

    public function testDot(): void
    {
        $array = [];
        $expected = [];
        $this->assertEquals($expected, Arr::dot($array));
        $array = ['key1' => 'value1', 'key2' => 'value2'];
        $expected = ['key1' => 'value1', 'key2' => 'value2'];
        $this->assertEquals($expected, Arr::dot($array));
        $array = ['key1' => 'value1', 'nested' => ['key2' => 'value2']];
        $expected = ['key1' => 'value1', 'nested.key2' => 'value2'];
        $this->assertEquals($expected, Arr::dot($array));
        $array = ['level1' => ['level2' => ['level3' => 'value3']]];
        $expected = ['level1.level2.level3' => 'value3'];
        $this->assertEquals($expected, Arr::dot($array));
        $array = ['key1' => 'value1', 'nested' => ['key2' => 123, 'key3' => true]];
        $expected = ['key1' => 'value1', 'nested.key2' => 123, 'nested.key3' => true];
        $this->assertEquals($expected, Arr::dot($array));
        $array = ['key1' => null, 'nested' => ['key2' => 'value2', 'key3' => null]];
        $expected = ['key1' => null, 'nested.key2' => 'value2', 'nested.key3' => null];
        $this->assertEquals($expected, Arr::dot($array));
        $array = ['key1' => 'value1', 'nested' => ['key2' => 'value2']];
        $prepend = 'prefix_';
        $expected = ['prefix_key1' => 'value1', 'prefix_nested.key2' => 'value2'];
        $this->assertEquals($expected, Arr::dot($array, $prepend));
    }

    public function testExists(): void
    {
        $array = ['key1' => 'value1', 'key2' => 'value2'];
        $this->assertTrue(Arr::exists($array, 'key1'));
        $this->assertFalse(Arr::exists($array, 'key3'));
        $arrayAccess = new ArrayObject(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertTrue(Arr::exists($arrayAccess, 'key1'));
        $this->assertFalse(Arr::exists($arrayAccess, 'key3'));
        $array = [];
        $this->assertFalse(Arr::exists($array, 'key1'));
        $arrayAccess = new ArrayObject([]);
        $this->assertFalse(Arr::exists($arrayAccess, 'key1'));
        $array = [0 => 'value1', 1 => 'value2'];
        $this->assertTrue(Arr::exists($array, 0));
        $this->assertFalse(Arr::exists($array, 2));
        $arrayAccess = new ArrayObject([0 => 'value1', 1 => 'value2']);
        $this->assertTrue(Arr::exists($arrayAccess, 0));
        $this->assertFalse(Arr::exists($arrayAccess, 2));
        $array = ['key1' => 'value1', 'key2' => 'value2'];
        $this->assertTrue(Arr::exists($array, 'key1'));
        $this->assertFalse(Arr::exists($array, 'nonexistent'));
    }

    public function testFirst(): void
    {
        $array = [1, 2, 3];
        $this->assertEquals('default', Arr::first([], null, 'default'));
        $this->assertEquals(1, Arr::first($array));
        $callback = static function ($value) {
            return $value > 1;
        };
        $this->assertEquals(2, Arr::first($array, $callback));
        $callback = static function ($value) {
            return $value > 3;
        };
        $default = 'default';
        $this->assertEquals('default', Arr::first($array, $callback, $default));
        $closure = static function ($value) {
            return $value === 2;
        };
        $this->assertEquals(2, Arr::first($array, $closure));
    }

    public function testLast(): void
    {
        $this->assertEquals('default', Arr::last([], null, 'default'));
        $this->assertEquals(3, Arr::last([1, 2, 3]));
        $callback = static function ($value) {
            return $value % 2 === 0;
        };
        $this->assertEquals(4, Arr::last([1, 2, 3, 4], $callback));
        $callback = static function ($value) {
            return $value % 2 === 0;
        };
        $this->assertEquals('default', Arr::last([1, 3, 5], $callback, 'default'));
        $closure = static function ($value) {
            return $value === 4; // 寻找特定的值4
        };
        $this->assertEquals(4, Arr::last([1, 2, 3, 4], $closure));
    }

    public function testFlatten(): void
    {
        $this->assertEquals(['item1', 'item2', 'item3'], Arr::flatten(['item1', 'item2', 'item3']));
        $this->assertEquals(['item1', 'item2', 'item3', 'item4', 'item5'], Arr::flatten(['item1', ['item2', 'item3'], ['item4', ['item5']]]));
        $this->assertEquals(['item1', 'item2', ['item3', 'item4'], 'item5'], Arr::flatten(['item1', ['item2', ['item3', 'item4']], ['item5']], 1));
        $this->assertEquals(['item1', 'item2', 'item3', 'item4'], Arr::flatten(['item1', 'item2', Collection::make(['item3', 'item4'])]));
        $this->assertEquals(['item1', 'item2', 'item3'], Arr::flatten(['item1', Collection::make(['item2', 'item3'])]));
    }

    public function testPluck(): void
    {
        $array = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];
        $value = 'name';
        $expected = ['John', 'Jane'];
        $this->assertEquals($expected, Arr::pluck($array, $value));
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ];
        $value = 'name';
        $key = 'id';
        $expected = [1 => 'John', 2 => 'Jane'];
        $this->assertEquals($expected, Arr::pluck($array, $value, $key));
        $array = [
            ['user' => ['name' => 'John']],
            ['user' => ['name' => 'Jane']],
        ];
        $value = 'user.name';
        $expected = ['John', 'Jane'];
        $this->assertEquals($expected, Arr::pluck($array, $value));
        $array = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];
        $value = 'age';
        $expected = [30, 25];
        $this->assertEquals($expected, Arr::pluck($array, $value));
        $array = [
            ['user' => ['name' => 'John', 'age' => 30]],
            ['user' => ['name' => 'Jane', 'age' => 25]],
        ];
        $this->assertEquals([30, 25], Arr::pluck($array, ['user', 'age']));
        $array = [
            ['id' => 1, 'user' => ['name' => 'John']],
            ['id' => 2, 'user' => ['name' => 'Jane']],
        ];
        $value = 'user.name';
        $key = ['id'];
        $expected = [1 => 'John', 2 => 'Jane'];
        $this->assertEquals($expected, Arr::pluck($array, $value, $key));
    }
}
