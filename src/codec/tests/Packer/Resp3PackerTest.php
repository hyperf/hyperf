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

use Hyperf\Codec\Packer\Resp3Packer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class Resp3PackerTest extends TestCase
{
    private Resp3Packer $packer;

    protected function setUp(): void
    {
        $this->packer = new Resp3Packer();
    }

    public function testPackAndUnpackNull()
    {
        $data = null;
        $packed = $this->packer->pack($data);
        $this->assertSame("_\r\n", $packed);
        $this->assertNull($this->packer->unpack($packed));
    }

    public function testPackAndUnpackBoolean()
    {
        $trueData = true;
        $packed = $this->packer->pack($trueData);
        $this->assertSame("#t\r\n", $packed);
        $this->assertTrue($this->packer->unpack($packed));

        $falseData = false;
        $packed = $this->packer->pack($falseData);
        $this->assertSame("#f\r\n", $packed);
        $this->assertFalse($this->packer->unpack($packed));
    }

    public function testPackAndUnpackInteger()
    {
        $data = 123;
        $packed = $this->packer->pack($data);
        $this->assertSame(":123\r\n", $packed);
        $this->assertSame(123, $this->packer->unpack($packed));

        $negativeData = -456;
        $packed = $this->packer->pack($negativeData);
        $this->assertSame(":-456\r\n", $packed);
        $this->assertSame(-456, $this->packer->unpack($packed));
    }

    public function testPackAndUnpackFloat()
    {
        $data = 123.45;
        $packed = $this->packer->pack($data);
        $this->assertSame(",123.45\r\n", $packed);
        $this->assertSame(123.45, $this->packer->unpack($packed));

        $negativeData = -67.89;
        $packed = $this->packer->pack($negativeData);
        $this->assertSame(",-67.89\r\n", $packed);
        $this->assertSame(-67.89, $this->packer->unpack($packed));
    }

    public function testPackAndUnpackString()
    {
        $data = 'hello';
        $packed = $this->packer->pack($data);
        $this->assertSame("$5\r\nhello\r\n", $packed);
        $this->assertSame('hello', $this->packer->unpack($packed));

        $emptyString = '';
        $packed = $this->packer->pack($emptyString);
        $this->assertSame("$0\r\n\r\n", $packed);
        $this->assertSame('', $this->packer->unpack($packed));

        $unicodeString = '你好';
        $packed = $this->packer->pack($unicodeString);
        $this->assertSame("$6\r\n你好\r\n", $packed);
        $this->assertSame('你好', $this->packer->unpack($packed));
    }

    public function testPackAndUnpackArray()
    {
        $data = [1, 'hello', true];
        $packed = $this->packer->pack($data);
        $expected = "*3\r\n:1\r\n$5\r\nhello\r\n#t\r\n";
        $this->assertSame($expected, $packed);
        $this->assertSame([1, 'hello', true], $this->packer->unpack($packed));

        $emptyArray = [];
        $packed = $this->packer->pack($emptyArray);
        $this->assertSame("*0\r\n", $packed);
        $this->assertSame([], $this->packer->unpack($packed));
    }

    public function testPackAndUnpackMap()
    {
        $data = ['name' => 'John', 'age' => 30];
        $packed = $this->packer->pack($data);
        $expected = "%2\r\n$4\r\nname\r\n$4\r\nJohn\r\n$3\r\nage\r\n:30\r\n";
        $this->assertSame($expected, $packed);
        $unpacked = $this->packer->unpack($packed);
        $this->assertSame(['name' => 'John', 'age' => 30], $unpacked);
    }

    public function testPackAndUnpackNestedStructures()
    {
        $data = [
            'users' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
            ],
            'count' => 2,
        ];
        $packed = $this->packer->pack($data);
        $unpacked = $this->packer->unpack($packed);
        $this->assertSame($data, $unpacked);
    }

    public function testUnpackSimpleString()
    {
        $data = "+OK\r\n";
        $unpacked = $this->packer->unpack($data);
        $this->assertSame('OK', $unpacked);
    }

    public function testUnpackError()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RESP3 Error: ERR unknown command');

        $data = "-ERR unknown command\r\n";
        $this->packer->unpack($data);
    }

    public function testUnpackNullBulkString()
    {
        $data = "$-1\r\n";
        $unpacked = $this->packer->unpack($data);
        $this->assertNull($unpacked);
    }

    public function testUnpackNullArray()
    {
        $data = "*-1\r\n";
        $unpacked = $this->packer->unpack($data);
        $this->assertNull($unpacked);
    }

    public function testUnpackSet()
    {
        $data = "~3\r\n:1\r\n:2\r\n:1\r\n";
        $unpacked = $this->packer->unpack($data);
        $this->assertSame([1, 2], $unpacked);
    }

    public function testPackUnsupportedType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported data type: resource');

        $resource = fopen('php://memory', 'r');
        $this->packer->pack($resource);
        fclose($resource);
    }

    public function testUnpackUnknownType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown RESP3 type: ?');

        $data = "?\r\n";
        $this->packer->unpack($data);
    }

    public function testUnpackUnexpectedEndOfData()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unexpected end of data');

        $data = '';
        $this->packer->unpack($data);
    }

    public function testUnpackExpectedCrlf()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected CRLF');

        $data = ":123\n";
        $this->packer->unpack($data);
    }

    public function testRoundTripComplexData()
    {
        $data = [
            'null' => null,
            'boolean' => true,
            'integer' => 42,
            'float' => 3.14,
            'string' => 'test',
            'array' => [1, 2, 3],
            'nested' => [
                'inner' => ['key' => 'value'],
            ],
        ];

        $packed = $this->packer->pack($data);
        $unpacked = $this->packer->unpack($packed);
        $this->assertSame($data, $unpacked);
    }
}
