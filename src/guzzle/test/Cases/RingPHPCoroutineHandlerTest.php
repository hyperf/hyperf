<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyerfTest\Guzzle\Cases;

use Hyperf\GuzzleHandler\RingPHP\CoroutineHandler;
use HyperfTest\Guzzle\TestCase;
use Swoole\Coroutine;

/**
 * @internal
 * @coversNothing
 */
class RingPHPCoroutineHandlerTest extends TestCase
{
    const URL = 'https://api.tb.swoft.lmx0536.cn';

    public function testUserInfo()
    {
        if (Coroutine::getuid() > 0) {
            $url = 'api.tb.swoft.lmx0536.cn';
            $handler = new CoroutineHandler();

            $res = $handler([
                'http_method' => 'GET',
                'headers' => ['host' => [$url]],
                'uri' => '/echo',
                'client' => [
                    'curl' => [
                        CURLOPT_USERPWD => 'username:password',
                    ],
                ],
            ]);

            $json = json_decode($res['body'], true);

            $this->assertEquals(0, $json['code']);
            $json = $json['data'];
            $this->assertEquals('Basic ' . base64_encode('username:password'), $json['headers']['authorization'][0]);
        }
        $this->assertTrue(true);
    }

    public function testCreatesErrors()
    {
        if (Coroutine::getuid() > 0) {
            $handler = new CoroutineHandler();
            $response = $handler([
                'http_method' => 'GET',
                'uri' => '/',
                'headers' => ['host' => [static::URL]],
                'client' => ['timeout' => 0.001],
            ]);

            $this->assertNull($response['status']);
            $this->assertNull($response['reason']);
            $this->assertEquals([], $response['headers']);
            $this->assertInstanceOf(
                'GuzzleHttp\Ring\Exception\RingException',
                $response['error']
            );

            $this->assertEquals(
                0,
                strpos('Connection timed out errCode=', $response['error']->getMessage())
            );
        }
    }

    public function testWithoutQuery()
    {
        if (Coroutine::getuid() > 0) {
            $url = 'api.tb.swoft.lmx0536.cn';
            $handler = new CoroutineHandler();

            $res = $handler([
                'http_method' => 'GET',
                'headers' => ['host' => [$url]],
                'uri' => '/echo?a=1&b=2',
            ]);

            $json = json_decode($res['body'], true);

            $this->assertEquals(0, $json['code']);
            $json = $json['data'];

            $this->assertEquals(1, $json['body']['a']);
            $this->assertEquals(2, $json['body']['b']);
        }
        $this->assertTrue(true);
    }
}
