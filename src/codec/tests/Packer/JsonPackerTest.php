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

use Hyperf\Codec\Packer\JsonPacker;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class JsonPackerTest extends TestCase
{
    public function testJsonEncodeAndDecode()
    {
        $packer = new JsonPacker();
        $this->assertSame('{"id":1}', $packer->pack(['id' => 1]));
        $this->assertSame('123123', $packer->pack(123123));
        $this->assertSame(['id' => 1], $packer->unpack('{"id":1}'));
    }

    public function testJsonDecodeFailed()
    {
        $packer = new JsonPacker();
        $this->assertSame(null, $packer->unpack('{"id":1'));
    }
}
