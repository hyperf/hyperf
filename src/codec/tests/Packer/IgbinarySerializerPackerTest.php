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

namespace HyperfTest\Codec\Packer;

use Exception;
use Hyperf\Codec\Packer\IgbinarySerializerPacker;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class IgbinarySerializerPackerTest extends TestCase
{
    public function testIgbinarySerializeAndUnserialize()
    {
        if (! file_exists('igbinary_serialize')) {
            $this->markTestSkipped("Don't support igbinary_serialize.");
        }
        $packer = new IgbinarySerializerPacker();
        $this->assertSame(igbinary_serialize(['id' => 1]), $packer->pack(['id' => 1]));
        $this->assertSame(igbinary_serialize(123123), $packer->pack(123123));
        $this->assertSame(['id' => 1], $packer->unpack(igbinary_serialize(['id' => 1])));
    }

    public function testIgbinaryUnserializeFailed()
    {
        if (! file_exists('igbinary_serialize')) {
            $this->markTestSkipped("Don't support igbinary_serialize.");
        }

        $packer = new IgbinarySerializerPacker();
        $this->expectException(Exception::class);
        $packer->unpack('invalid binary string');
    }
}
