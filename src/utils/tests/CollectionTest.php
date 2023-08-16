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
namespace HyperfTest\Utils;

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

    public function testInstanceofCollection()
    {
        $col = new Collection([]);
        $this->assertTrue($col instanceof Collection);
        $this->assertTrue($col instanceof \Hyperf\Utils\Collection);

        $col = new \Hyperf\Utils\Collection([]);
        $this->assertTrue($col instanceof Collection);
        $this->assertTrue($col instanceof \Hyperf\Utils\Collection);
    }
}
