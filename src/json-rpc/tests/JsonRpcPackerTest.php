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

use Hyperf\Config\Config;
use Hyperf\JsonRpc\Packer\JsonRpcPacker;
use Hyperf\Utils\Str;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonRpcPackerTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testPackOpenEofCheck()
    {
        $config = new Config([
            'json_rpc' => [
                'transporter' => [
                    'tcp' => [
                        'options' => [
                            'open_eof_check' => true,
                            'package_max_length' => 1024 * 1024 * 2,
                        ],
                    ],
                ],
            ],
        ]);

        $packer = new JsonRpcPacker($config);
        $string = $packer->pack(['id' => 1]);
        $this->assertTrue(Str::endsWith($string, "\r\n"));

        $config = new Config([
            'json_rpc' => [
                'transporter' => [
                    'tcp' => [
                        'options' => [
                            'open_eof_check' => true,
                            'package_eof' => "\r\n\r\n",
                            'package_max_length' => 1024 * 1024 * 2,
                        ],
                    ],
                ],
            ],
        ]);

        $packer = new JsonRpcPacker($config);
        $string = $packer->pack(['id' => 1]);
        $this->assertTrue(Str::endsWith($string, "\r\n\r\n"));

        $ref = new \ReflectionClass($packer);
        $method = $ref->getMethod('isLengthCheck');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($packer));

        $method = $ref->getMethod('getEof');
        $method->setAccessible(true);
        $this->assertSame("\r\n\r\n", $method->invoke($packer));
    }

    public function testPackOpenLengthCheck()
    {
        $config = new Config([
            'json_rpc' => [
                'transporter' => [
                    'tcp' => [
                        'options' => [
                            'open_length_check' => true,
                            'package_length_type' => 'N',
                            'package_length_offset' => 0,
                            'package_body_offset' => 4,
                        ],
                    ],
                ],
            ],
        ]);

        $packer = new JsonRpcPacker($config);
        $string = $packer->pack($data = ['id' => 1]);
        $expected = json_encode($data);
        $this->assertSame(pack('N', strlen($expected)) . $expected, $string);
        $this->assertSame($data, $packer->unpack($string));

        $ref = new \ReflectionClass($packer);
        $method = $ref->getMethod('isLengthCheck');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($packer));

        $method = $ref->getMethod('getPackType');
        $method->setAccessible(true);
        $this->assertSame('N', $method->invoke($packer));

        $method = $ref->getMethod('getHeadLength');
        $method->setAccessible(true);
        $this->assertSame(4, $method->invoke($packer));
    }
}
