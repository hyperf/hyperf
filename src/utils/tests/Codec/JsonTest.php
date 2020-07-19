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
namespace HyperfTest\Utils\Codec;

use Hyperf\Utils\Codec\Json;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonTest extends TestCase
{
    public function testEncode()
    {
        $data = [
            'name' => 'Hyperf',
        ];
        $json = '{"name":"Hyperf"}';
        $this->assertSame($json, Json::encode($data));
    }

    public function testDecode()
    {
        $data = [
            'name' => 'Hyperf',
        ];
        $json = '{"name":"Hyperf"}';
        $this->assertSame($data, Json::decode($json));
    }

    public function testDecodeToObject()
    {
        $data = [
            'name' => 'Hyperf',
        ];
        $json = '{"name":"Hyperf"}';
        $this->assertEquals((object) $data, Json::decode($json, false));
    }

    public function testDecodeException()
    {
        $json = '{"name":"Hyperf}';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Control character error, possibly incorrectly encoded');
        Json::decode($json);
    }

    public function testJsonEncodeInCoroutine()
    {
        $result = null;
        go(function () use (&$result) {
            $result = Json::encode([1, 2, 3]);
        });
        $this->assertSame('[1,2,3]', $result);
        go(function () use (&$result) {
            $result = Json::decode('[1,2,3]');
        });
        $this->assertSame([1, 2, 3], $result);
    }
}
