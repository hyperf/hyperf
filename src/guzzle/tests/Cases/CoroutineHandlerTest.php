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
namespace HyperfTest\Guzzle\Cases;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Hyperf\Guzzle\CoroutineHandler;
use HyperfTest\Guzzle\Stub\CoroutineHandlerStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Swoole\Coroutine\Http\Client as SwooleHttpClient;

/**
 * @internal
 * @coversNothing
 */
class CoroutineHandlerTest extends TestCase
{
    public function testCreatesCurlErrors()
    {
        $handler = new CoroutineHandler();
        $request = new Request('GET', 'http://localhost:123');
        try {
            $handler($request, ['timeout' => 0.001, 'connect_timeout' => 0.001])->wait();
        } catch (\Exception $ex) {
            $this->assertInstanceOf(ConnectException::class, $ex);
            $this->assertEquals(0, strpos($ex->getMessage(), 'Connection timed out errCode='));
        }
    }

    public function testReusesHandles()
    {
        $a = new CoroutineHandler();
        $request = new Request('GET', 'https://pokeapi.co/api/v2/pokemon/');
        $r1 = $a($request, []);
        $request = new Request('GET', 'https://pokeapi.co/api/v2/pokemon/');
        $r2 = $a($request, []);

        $this->assertInstanceOf(PromiseInterface::class, $r1);
        $this->assertInstanceOf(PromiseInterface::class, $r2);
    }

    public function testDoesSleep()
    {
        $a = new CoroutineHandlerStub();
        $request = new Request('GET', 'https://pokeapi.co/api/v2/pokemon/');
        $resposne = $a($request, ['delay' => 1, 'timeout' => 5])->wait();

        $json = json_decode($resposne->getBody()->getContents(), true);

        $this->assertSame(5, $json['setting']['timeout']);
    }

    public function testCreatesErrorsWithContext()
    {
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

        $client = new Client([
            'base_uri' => 'https://pokeapi.co',
            'timeout' => 5,
            'handler' => HandlerStack::create(new CoroutineHandler()),
        ]);

        $response = $client->get('/api/v2/pokemon')->getBody()->getContents();

        $this->assertNotEmpty($response);
    }

    public function testSwooleSetting()
    {
        $client = new Client([
            'base_uri' => 'http://127.0.0.1:8080',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'timeout' => 5,
            'swoole' => [
                'timeout' => 10,
                'socket_buffer_size' => 1024 * 1024 * 2,
            ],
        ]);

        $data = json_decode($client->get('/')->getBody()->getContents(), true);

        $this->assertSame(10, $data['setting']['timeout']);
        $this->assertSame(1024 * 1024 * 2, $data['setting']['socket_buffer_size']);
    }

    public function testProxy()
    {
        $client = new Client([
            'base_uri' => 'http://127.0.0.1:8080',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'proxy' => 'http://user:pass@127.0.0.1:8081',
        ]);

        $json = json_decode($client->get('/')->getBody()->getContents(), true);

        $setting = $json['setting'];

        $this->assertSame('127.0.0.1', $setting['http_proxy_host']);
        $this->assertSame(8081, $setting['http_proxy_port']);
        $this->assertSame('user', $setting['http_proxy_user']);
        $this->assertSame('pass', $setting['http_proxy_password']);
    }

    public function testProxyArrayHttpScheme()
    {
        $client = new Client([
            'base_uri' => 'http://127.0.0.1:8080',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'proxy' => [
                'http' => 'http://127.0.0.1:12333',
                'https' => 'http://127.0.0.1:12334',
                'no' => ['.cn'],
            ],
        ]);

        $json = json_decode($client->get('/')->getBody()->getContents(), true);

        $setting = $json['setting'];

        $this->assertSame('127.0.0.1', $setting['http_proxy_host']);
        $this->assertSame(12333, $setting['http_proxy_port']);
        $this->assertArrayNotHasKey('http_proxy_user', $setting);
        $this->assertArrayNotHasKey('http_proxy_password', $setting);
    }

    public function testProxyArrayHttpsScheme()
    {
        $client = new Client([
            'base_uri' => 'https://www.baidu.com',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'proxy' => [
                'http' => 'http://127.0.0.1:12333',
                'https' => 'http://127.0.0.1:12334',
                'no' => ['.cn'],
            ],
        ]);

        $json = json_decode($client->get('/')->getBody()->getContents(), true);

        $setting = $json['setting'];

        $this->assertSame('127.0.0.1', $setting['http_proxy_host']);
        $this->assertSame(12334, $setting['http_proxy_port']);
        $this->assertArrayNotHasKey('http_proxy_user', $setting);
        $this->assertArrayNotHasKey('http_proxy_password', $setting);
    }

    public function testProxyArrayHostInNoproxy()
    {
        $client = new Client([
            'base_uri' => 'https://www.baidu.cn',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'proxy' => [
                'http' => 'http://127.0.0.1:12333',
                'https' => 'http://127.0.0.1:12334',
                'no' => ['.cn'],
            ],
        ]);

        $json = json_decode($client->get('/')->getBody()->getContents(), true);

        $setting = $json['setting'];

        $this->assertArrayNotHasKey('http_proxy_host', $setting);
        $this->assertArrayNotHasKey('http_proxy_port', $setting);
    }

    public function testSslKeyAndCert()
    {
        $client = new Client([
            'base_uri' => 'http://127.0.0.1:8080',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'timeout' => 5,
            'cert' => 'apiclient_cert.pem',
            'ssl_key' => 'apiclient_key.pem',
        ]);

        $data = json_decode($client->get('/')->getBody()->getContents(), true);

        $this->assertSame('apiclient_cert.pem', $data['setting']['ssl_cert_file']);
        $this->assertSame('apiclient_key.pem', $data['setting']['ssl_key_file']);

        $client = new Client([
            'base_uri' => 'http://127.0.0.1:8080',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'timeout' => 5,
        ]);

        $data = json_decode($client->get('/')->getBody()->getContents(), true);

        $this->assertArrayNotHasKey('ssl_cert_file', $data['setting']);
        $this->assertArrayNotHasKey('ssl_key_file', $data['setting']);
    }

    public function testUserInfo()
    {
        $url = 'https://username:password@127.0.0.1:8080';
        $handler = new CoroutineHandlerStub();
        $request = new Request('GET', $url . '/echo');

        $res = $handler($request, ['timeout' => 5])->wait();
        $content = $res->getBody()->getContents();
        $json = json_decode($content, true);

        $this->assertEquals('Basic ' . base64_encode('username:password'), $json['headers']['Authorization']);
    }

    public function testStatusCode()
    {
        $client = new SwooleHttpClient('127.0.0.1', 80);
        $client->statusCode = -1;
        $request = \Mockery::mock(RequestInterface::class);
        $handler = new CoroutineHandlerStub();
        $ex = $handler->checkStatusCode($client, $request);
        $this->assertInstanceOf(ConnectException::class, $ex);

        $client = new SwooleHttpClient('127.0.0.1', 80);
        $client->statusCode = -2;
        $request = \Mockery::mock(RequestInterface::class);
        $handler = new CoroutineHandlerStub();
        $ex = $handler->checkStatusCode($client, $request);
        $this->assertInstanceOf(RequestException::class, $ex);

        $client = new SwooleHttpClient('127.0.0.1', 80);
        $client->statusCode = -3;
        $request = \Mockery::mock(RequestInterface::class);
        $handler = new CoroutineHandlerStub();
        $ex = $handler->checkStatusCode($client, $request);
        $this->assertInstanceOf(RequestException::class, $ex);
        $this->assertSame('Server reset', $ex->getMessage());
    }

    public function testRequestOptionOnStats()
    {
        $url = 'http://127.0.0.1:9501';
        $handler = new CoroutineHandlerStub();
        $request = new Request('GET', $url . '/echo');

        $bool = false;
        $handler($request, [RequestOptions::ON_STATS => function (TransferStats $stats) use (&$bool) {
            $bool = true;
            $this->assertIsFloat($stats->getTransferTime());
        }])->wait();
        $this->assertTrue($bool);
    }

    public function testRequestOptionOnStatsInClient()
    {
        $bool = false;
        $url = 'http://127.0.0.1:9501';
        $client = new Client([
            'handler' => new CoroutineHandlerStub(),
            'base_uri' => $url,
            RequestOptions::ON_STATS => function (TransferStats $stats) use (&$bool) {
                $bool = true;
                $this->assertIsFloat($stats->getTransferTime());
            },
        ]);
        $client->get('/');
        $this->assertTrue($bool);
    }

    protected function getHandler($options = [])
    {
        return new CoroutineHandler($options);
    }
}
