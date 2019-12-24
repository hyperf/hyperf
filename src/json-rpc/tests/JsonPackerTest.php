<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\JsonRpc;

use Hyperf\JsonRpc\Packer\JsonEofPacker;
use Hyperf\JsonRpc\Packer\JsonLengthPacker;
use Hyperf\Utils\Str;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonPackerTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testJsonEofPacker()
    {
        $packer = new JsonEofPacker([
            'settings' => [
                'package_eof' => "\r\n",
            ],
        ]);

        $string = $packer->pack(['id' => 1]);
        $this->assertTrue(Str::endsWith($string, "\r\n"));

        $packer = new JsonEofPacker([
            'settings' => [
                'package_eof' => "\r\n\r\n",
            ],
        ]);
        $string = $packer->pack(['id' => 1]);
        $this->assertTrue(Str::endsWith($string, "\r\n\r\n"));
    }

    public function testPackOpenLengthCheck()
    {
        $packer = new JsonLengthPacker([
            'settings' => [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
            ],
        ]);
        $string = $packer->pack($data = ['id' => 1]);
        $expected = json_encode($data);
        $this->assertSame(pack('N', strlen($expected)) . $expected, $string);
        $this->assertSame($data, $packer->unpack($string));
    }
}
