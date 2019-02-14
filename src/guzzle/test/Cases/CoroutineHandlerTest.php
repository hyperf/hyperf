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

namespace HyperfTest\Guzzle\Cases;

use Swoole\Coroutine;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Hyperf\Guzzle\CoroutineHandler;
use GuzzleHttp\Exception\ConnectException;

/**
 * @internal
 * @coversNothing
 */
class CoroutineHandlerTest extends TestCase
{
    const URL = 'https://api.tb.swoft.lmx0536.cn';

    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testCreatesCurlErrors()
    {
        if (Coroutine::getuid() > 0) {
            $handler = new CoroutineHandler();
            $request = new Request('GET', 'http://localhost:123');
            try {
                $handler($request, ['timeout' => 0.001, 'connect_timeout' => 0.001])->wait();
            } catch (\Exception $ex) {
                $this->assertInstanceOf(ConnectException::class, $ex);
                $this->assertEquals(0, strpos($ex->getMessage(), 'Connection timed out errCode='));
            }
        }
        $this->assertTrue(true);
    }

    public function testReusesHandles()
    {
        if (Coroutine::getuid() > 0) {
            $a = new CoroutineHandler();
            $request = new Request('GET', static::URL);
            $a($request, []);
            $a($request, []);
        }
        $this->assertTrue(true);
    }

    public function testDoesSleep()
    {
        if (Coroutine::getuid() > 0) {
            $a = new CoroutineHandler();
            $request = new Request('GET', static::URL);
            $s = microtime(true);
            $a($request, ['delay' => 1, 'timeout' => 5])->wait();
            $this->assertGreaterThan(0.001, microtime(true) - $s);
        }
        $this->assertTrue(true);
    }

    public function testCreatesErrorsWithContext()
    {
        if (Coroutine::getuid() > 0) {
            $handler = new CoroutineHandler();
            $request = new Request('GET', 'http://localhost:123');
            $called = false;
            $p = $handler($request, ['timeout' => 0.001])
                ->otherwise(function (ConnectException $e) use (&$called) {
                    $called = true;
                    $this->assertArrayHasKey('errCode', $e->getHandlerContext());
                    $this->assertArrayHasKey('statusCode', $e->getHandlerContext());
                });
            $p->wait();
            $this->assertTrue($called);
        }

        $this->assertTrue(true);
    }

    public function testGuzzleClient()
    {
        if (Coroutine::getuid() > 0) {
            $client = new Client([
                'base_uri' => static::URL,
            ]);
            $res = $client->get('/echo', [
                'timeout' => 10,
                'headers' => [
                    'X-TOKEN' => md5('1234'),
                ],
                'json' => [
                    'id' => 1,
                ],
            ])->getBody()->getContents();
            $res = \GuzzleHttp\json_decode($res, true);

            $this->assertEquals(0, $res['code']);
            $res = $res['data'];
            $this->assertEquals(md5('1234'), $res['headers']['x-token'][0]);
            $this->assertEquals(1, $res['json']['id']);
        }

        $this->assertTrue(true);
    }

    public function testUserInfo()
    {
        if (Coroutine::getuid() > 0) {
            $url = 'https://username:password@api.tb.swoft.lmx0536.cn';
            $handler = new CoroutineHandler();
            $request = new Request('GET', $url . '/echo');

            $res = $handler($request, ['timeout' => 5])->wait();
            $content = $res->getBody()->getContents();
            $json = json_decode($content, true);

            $this->assertEquals(0, $json['code']);
            $json = $json['data'];
            $this->assertEquals('Basic ' . base64_encode('username:password'), $json['headers']['authorization'][0]);
        }

        $this->assertTrue(true);
    }

    protected function getHandler($options = [])
    {
        return new CoroutineHandler($options);
    }
}
