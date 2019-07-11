<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Guzzle\Cases;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Hyperf\Guzzle\CoroutineHandler;
use HyperfTest\Guzzle\Stub\CoroutineHandlerStub;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

/**
 * @internal
 * @coversNothing
 */
class CoroutineHandlerTest extends TestCase
{
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
            $request = new Request('GET', 'https://api.github.com/');
            $a($request, []);
            $a($request, []);
        }
        $this->assertTrue(true);
    }

    public function testDoesSleep()
    {
        $a = new CoroutineHandlerStub();
        $request = new Request('GET', 'https://api.github.com/');
        $resposne = $a($request, ['delay' => 1, 'timeout' => 5])->wait();

        $json = json_decode($resposne->getBody()->getContents(), true);

        $this->assertSame(5, $json['setting']['timeout']);
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
        $client = new Client([
            'base_uri' => 'http://127.0.0.1:8080',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
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

        $this->assertSame('127.0.0.1', $res['host']);
        $this->assertSame(8080, $res['port']);
        $this->assertSame(false, $res['ssl']);
        $this->assertSame(md5('1234'), $res['headers']['X-TOKEN']);
    }

    public function testUserInfo()
    {
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

    protected function getHandler($options = [])
    {
        return new CoroutineHandler($options);
    }
}
