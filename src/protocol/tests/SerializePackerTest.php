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

namespace HyperfTest\Protocol;

use Hyperf\Protocol\Packer\SerializePacker;
use HyperfTest\Protocol\Stub\DemoStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class SerializePackerTest extends TestCase
{
    public function testPack()
    {
        $packer = new SerializePacker();
        $obj = new DemoStub();
        $string = $packer->pack($obj);

        $len = $packer->length(substr($string, 0, SerializePacker::HEAD_LENGTH));
        $this->assertSame($len + SerializePacker::HEAD_LENGTH, strlen($string));

        $target = $packer->unpack($string);
        $this->assertInstanceOf(DemoStub::class, $target);
        $this->assertSame($obj->unique, $target->unique);
    }
}
