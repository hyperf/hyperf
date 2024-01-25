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
namespace HyperfTest\Collections;

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
    public function testOperatorForWhere()
    {
        $col = new Collection([['id' => 1, 'name' => 'Hyperf'], ['id' => 2, 'name' => 'HyperfCloud']]);

        $res = $col->where('id', 1);
        $this->assertSame(1, $res->count());
        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], $res->shift());

        $res = $col->where('id', '=', 2);
        $this->assertSame(1, $res->count());
        $this->assertSame(['id' => 2, 'name' => 'HyperfCloud'], $res->shift());
    }

    public function testRandom()
    {
        $col = new Collection([['id' => 1, 'name' => 'Hyperf'], ['id' => 2, 'name' => 'HyperfCloud']]);

        $res = $col->random();
        $this->assertTrue(is_array($res));

        $res = $col->random(1);
        $this->assertTrue($res instanceof Collection);
    }

    public function testFlatten()
    {
        $collection = new Collection([
            'item' => [
                'name' => 'Hyperf',
            ],
            'it' => [
                'id' => $uuid = uniqid(),
            ],
        ]);

        $this->assertSame(['Hyperf', $uuid], $collection->flatten()->toArray());
    }

    public function testCollectionAverage()
    {
        $col = new Collection([]);
        $this->assertNull($col->avg());
    }

    public function testMapWithKeys()
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

    public function testMapWithKeysIntegerKeys()
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

    public function testMapWithKeysMultipleRows()
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

    public function testMapWithKeysCallbackKey()
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

    public function testForgetSingleKey()
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

    public function testForgetArrayOfKeys()
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

    public function testForgetCollectionOfKeys()
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

    public function testExcept()
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

    public function testReplaceNull()
    {
        $c = new Collection(['a', 'b', 'c']);
        $this->assertEquals(['a', 'b', 'c'], $c->replace(null)->all());
    }

    public function testReplaceArray()
    {
        $c = new Collection(['a', 'b', 'c']);
        $this->assertEquals(['a', 'd', 'e'], $c->replace([1 => 'd', 2 => 'e'])->all());

        $c = new Collection(['a', 'b', 'c']);
        $this->assertEquals(['a', 'd', 'e', 'f', 'g'], $c->replace([1 => 'd', 2 => 'e', 3 => 'f', 4 => 'g'])->all());

        $c = new Collection(['name' => 'amir', 'family' => 'otwell']);
        $this->assertEquals(['name' => 'taylor', 'family' => 'otwell', 'age' => 26], $c->replace(['name' => 'taylor', 'age' => 26])->all());
    }

    public function testReplaceCollection()
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

    public function testReplaceRecursiveNull()
    {
        $c = new Collection(['a', 'b', ['c', 'd']]);
        $this->assertEquals(['a', 'b', ['c', 'd']], $c->replaceRecursive(null)->all());
    }

    public function testReplaceRecursiveArray()
    {
        $c = new Collection(['a', 'b', ['c', 'd']]);
        $this->assertEquals(['z', 'b', ['c', 'e']], $c->replaceRecursive(['z', 2 => [1 => 'e']])->all());

        $c = new Collection(['a', 'b', ['c', 'd']]);
        $this->assertEquals(['z', 'b', ['c', 'e'], 'f'], $c->replaceRecursive(['z', 2 => [1 => 'e'], 'f'])->all());
    }

    public function testReplaceRecursiveCollection()
    {
        $c = new Collection(['a', 'b', ['c', 'd']]);
        $this->assertEquals(
            ['z', 'b', ['c', 'e']],
            $c->replaceRecursive(new Collection(['z', 2 => [1 => 'e']]))->all()
        );
    }
}
