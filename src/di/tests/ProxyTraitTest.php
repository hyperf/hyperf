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

namespace HyperfTest\Di;

use HyperfTest\Di\Stub\ProxyTraitObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProxyTraitTest extends TestCase
{
    public function testGetParamsMap()
    {
        $obj = new ProxyTraitObject();

        $this->assertEquals(['id' => null, 'str' => ''], $obj->get(null)['keys']);
        $this->assertEquals(['id', 'str'], $obj->get(null)['order']);

        $this->assertEquals(['id' => 1, 'str' => ''], $obj->get2()['keys']);
        $this->assertEquals(['id', 'str'], $obj->get2()['order']);

        $this->assertEquals(['id' => null, 'str' => ''], $obj->get2(null)['keys']);
        $this->assertEquals(['id', 'str'], $obj->get2(null)['order']);

        $this->assertEquals(['id' => 1, 'str' => '', 'num' => 1.0], $obj->get3()['keys']);
        $this->assertEquals(['id', 'str', 'num'], $obj->get3()['order']);

        $this->assertEquals(['id' => 1, 'str' => 'hy', 'num' => 1.0], $obj->get3(1, 'hy')['keys']);
        $this->assertEquals(['id', 'str', 'num'], $obj->get3(1, 'hy')['order']);
    }
}
