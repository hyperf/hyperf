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
namespace HyperfTest\Guzzle\Cases;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Hyperf\Guzzle\CoroutineHandler;
use HyperfTest\Guzzle\Stub\CoroutineHandlerStub;
use Mockery;
use PHPUnit\Framework\TestCase;

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
        } catch (Exception $ex) {
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
        $response = $a($request, ['delay' => 1, 'timeout' => 5])->wait();

        $json = json_decode((string) $response->getBody(), true);

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

        $res = (string) $client->get('/echo', [
            'timeout' => 10,
            'headers' => [
                'X-TOKEN' => md5('1234'),
            ],
            'json' => [
                'id' => 1,
            ],
        ])->getBody();

        $res = \GuzzleHttp\json_decode($res, true);

        $this->assertSame('127.0.0.1', $res['host']);
        $this->assertSame(8080, $res['port']);
        $this->assertSame(false, $res['ssl']);
        $this->assertSame([md5('1234')], $res['headers']['X-TOKEN']);

        $client = new Client([
            'base_uri' => 'https://pokeapi.co',
            'timeout' => 5,
            'handler' => HandlerStack::create(new CoroutineHandler()),
        ]);

        $response = (string) $client->get('/api/v2/pokemon')->getBody();

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

        $data = json_decode((string) $client->get('/')->getBody(), true);

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

        $json = json_decode((string) $client->get('/')->getBody(), true);

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

        $json = json_decode((string) $client->get('/')->getBody(), true);

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

        $json = json_decode((string) $client->get('/')->getBody(), true);

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

        $json = json_decode((string) $client->get('/')->getBody(), true);

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

        $data = json_decode((string) $client->get('/')->getBody(), true);

        $this->assertSame('apiclient_cert.pem', $data['setting']['ssl_cert_file']);
        $this->assertSame('apiclient_key.pem', $data['setting']['ssl_key_file']);

        $client = new Client([
            'base_uri' => 'http://127.0.0.1:8080',
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'timeout' => 5,
        ]);

        $data = json_decode((string) $client->get('/')->getBody(), true);

        $this->assertArrayNotHasKey('ssl_cert_file', $data['setting']);
        $this->assertArrayNotHasKey('ssl_key_file', $data['setting']);
    }

    public function testUserInfo()
    {
        $url = 'https://username:password@127.0.0.1:8080';
        $handler = new CoroutineHandlerStub();
        $request = new Request('GET', $url . '/echo');

        $res = $handler($request, ['timeout' => 5])->wait();
        $content = (string) $res->getBody();
        $json = json_decode($content, true);

        $this->assertEquals('Basic ' . base64_encode('username:password'), $json['headers']['Authorization']);
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

    public function testSink()
    {
        $dir = BASE_PATH . '/runtime/guzzle/';
        @mkdir($dir, 0755, true);

        $handler = new CoroutineHandlerStub();
        $stream = $handler->createSink($body = uniqid(), $sink = $dir . uniqid());
        $this->assertSame($body, file_get_contents($sink));
        $this->assertSame('', stream_get_contents($stream));

        $stream = $handler->createSink($body = uniqid(), $sink);
        $this->assertSame($body, file_get_contents($sink));
        $this->assertSame('', stream_get_contents($stream));
        fseek($stream, 0);
        $this->assertSame($body, stream_get_contents($stream));
    }

    public function testResourceSink()
    {
        $dir = BASE_PATH . '/runtime/guzzle/';
        @mkdir($dir, 0755, true);
        $sink = fopen($file = $dir . uniqid(), 'w+');
        $handler = new CoroutineHandlerStub();
        $stream = $handler->createSink($body1 = uniqid(), $sink);
        $this->assertSame('', stream_get_contents($stream));
        $stream = $handler->createSink($body2 = uniqid(), $sink);
        $this->assertSame('', stream_get_contents($stream));
        $this->assertSame($body1 . $body2, file_get_contents($file));
        fseek($sink, 0);
        $this->assertSame($body1 . $body2, stream_get_contents($stream));
    }

    public function testExpect100Continue()
    {
        $url = 'http://127.0.0.1:9501';
        $client = new Client([
            'handler' => HandlerStack::create(new CoroutineHandlerStub()),
            'base_uri' => $url,
        ]);
        $res = $client->post('/', [
            RequestOptions::JSON => [
                'data' => str_repeat($id = uniqid(), 100000),
            ],
        ]);

        $data = json_decode((string) $res->getBody(), true);
        $this->assertArrayNotHasKey('Content-Length', $data['headers']);
        $this->assertArrayNotHasKey('Expect', $data['headers']);

        $stub = Mockery::mock(CoroutineHandlerStub::class . '[rewriteHeaders]');
        $stub->shouldReceive('rewriteHeaders')->withAnyArgs()->andReturnUsing(function ($headers) {
            return $headers;
        });

        $client = new Client([
            'handler' => HandlerStack::create($stub),
            'base_uri' => $url,
        ]);
        $res = $client->post('/', [
            RequestOptions::JSON => [
                'data' => str_repeat($id = uniqid(), 100000),
            ],
        ]);

        $data = json_decode((string) $res->getBody(), true);
        $this->assertArrayHasKey('Content-Length', $data['headers']);
        $this->assertArrayHasKey('Expect', $data['headers']);
    }

    protected function getHandler($options = [])
    {
        return new CoroutineHandler($options);
    }
}
