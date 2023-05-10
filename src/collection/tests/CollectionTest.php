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
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
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
}
