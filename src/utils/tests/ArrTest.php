<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Utils;

use Hyperf\Utils\Arr;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ArrTest extends TestCase
{
    public function testArrGet()
    {
        $data = ['id' => 1, 'name' => 'Hyperf'];
        $this->assertSame(1, Arr::get($data, 'id'));
        $this->assertSame('Hyperf', Arr::get($data, 'name'));
        $this->assertSame($data, Arr::get($data));
        $this->assertSame(null, Arr::get($data, 'gendar'));
        $this->assertSame(1, Arr::get($data, 'gendar', 1));

        $data = [1, 2, 3, 4];
        $this->assertSame(1, Arr::get($data, 0));
        $this->assertSame(5, Arr::get($data, 4, 5));
        $this->assertSame(null, Arr::get($data, 5));

        $object = new \stdClass();
        $object->id = 1;
        $this->assertSame(null, Arr::get($object, 'id'));
    }
}
