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
namespace HyerfTest\Guzzle\Cases;

use GuzzleHttp\Ring\Exception\RingException;
use Hyperf\Guzzle\RingPHP\CoroutineHandler;
use HyperfTest\Guzzle\Stub\RingPHPCoroutineHanderStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Swoole\Coroutine\Http\Client as SwooleHttpClient;

/**
 * @internal
 * @coversNothing
 */
class RingPHPCoroutineHandlerTest extends TestCase
{
    public function testUserInfo()
    {
        $handler = new RingPHPCoroutineHanderStub();

        $res = $handler([
            'http_method' => 'GET',
            'headers' => ['host' => ['127.0.0.1:8080']],
            'uri' => '/echo',
            'client' => [
                'curl' => [
                    CURLOPT_USERPWD => 'username:password',
                ],
            ],
        ]);

        $json = json_decode(stream_get_contents($res['body']), true);

        $this->assertEquals('Basic ' . base64_encode('username:password'), $json['headers']['Authorization']);
    }

    public function testCreatesErrors()
    {
        $handler = new CoroutineHandler();
        $response = $handler([
            'http_method' => 'GET',
            'uri' => '/',
            'headers' => ['host' => ['127.0.0.1:8080']],
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

    public function testWithoutQuery()
    {
        $handler = new RingPHPCoroutineHanderStub();

        $res = $handler([
            'http_method' => 'GET',
            'headers' => ['host' => ['127.0.0.1:8080']],
            'uri' => '/echo?a=1&b=2',
        ]);

        $json = json_decode(stream_get_contents($res['body']), true);

        $this->assertEquals('/echo?a=1&b=2', $json['uri']);
    }

    public function testStatusCode()
    {
        $client = new SwooleHttpClient('127.0.0.1', 80);
        $client->statusCode = -1;
        $request = \Mockery::mock(RequestInterface::class);
        $handler = new RingPHPCoroutineHanderStub();
        $ex = $handler->checkStatusCode($client, $request);
        $this->assertInstanceOf(RingException::class, $ex);

        $client = new SwooleHttpClient('127.0.0.1', 80);
        $client->statusCode = -2;
        $request = \Mockery::mock(RequestInterface::class);
        $handler = new RingPHPCoroutineHanderStub();
        $ex = $handler->checkStatusCode($client, $request);
        $this->assertInstanceOf(RingException::class, $ex);
        $this->assertSame('Request timed out', $ex->getMessage());

        $client = new SwooleHttpClient('127.0.0.1', 80);
        $client->statusCode = -3;
        $request = \Mockery::mock(RequestInterface::class);
        $handler = new RingPHPCoroutineHanderStub();
        $ex = $handler->checkStatusCode($client, $request);
        $this->assertInstanceOf(RingException::class, $ex);
        $this->assertSame('Server reset', $ex->getMessage());
    }
}
