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

use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coroutine\go;

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

    public function testJsonFailed()
    {
        $this->expectException(InvalidArgumentException::class);
        Json::decode('{"hype');
    }
}
