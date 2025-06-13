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

namespace HyperfTest\HttpServer;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Xmlable;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Response;
use Hyperf\HttpServer\ResponseEmitter;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use ReflectionClass;
use Swoole\Http\Response as SwooleResponse;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class ResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set(PsrResponseInterface::class, null);
    }

    public function testRedirect()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        RequestContext::set(new Request('GET', 'http://127.0.0.1:9501'));
        $container->shouldReceive('get')->with(RequestInterface::class)->andReturn($request);
        ApplicationContext::setContainer($container);

        $psrResponse = new \Hyperf\HttpMessage\Base\Response();
        Context::set(PsrResponseInterface::class, $psrResponse);

        $response = new Response();
        $res = $response->redirect('https://www.baidu.com');

        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('https://www.baidu.com', $res->getHeaderLine('Location'));

        $response = new Response();
        $res = $response->redirect('http://www.baidu.com');

        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('http://www.baidu.com', $res->getHeaderLine('Location'));

        $response = new Response();
        $res = $response->redirect('/index');
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('http://127.0.0.1:9501/index', $res->getHeaderLine('Location'));

        $response = new Response();
        $res = $response->redirect('index');
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('http://127.0.0.1:9501/index', $res->getHeaderLine('Location'));
    }

    public function testToXml()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $psrResponse = new \Hyperf\HttpMessage\Base\Response();
        Context::set(PsrResponseInterface::class, $psrResponse);

        $response = new Response();
        $reflectionClass = new ReflectionClass(Response::class);
        $reflectionMethod = $reflectionClass->getMethod('toXml');

        $expected = '<?xml version="1.0" encoding="utf-8"?>
<root><kstring>string</kstring><kint1>1</kint1><kint0>0</kint0><kfloat>0.12345</kfloat><kfalse/><ktrue>1</ktrue><karray><kstring>string</kstring><kint1>1</kint1><kint0>0</kint0><kfloat>0.12345</kfloat><kfalse/><ktrue>1</ktrue></karray></root>';

        // Array
        $this->assertSame($expected, $reflectionMethod->invoke($response, [
            'kstring' => 'string',
            'kint1' => 1,
            'kint0' => 0,
            'kfloat' => 0.12345,
            'kfalse' => false,
            'ktrue' => true,
            'karray' => [
                'kstring' => 'string',
                'kint1' => 1,
                'kint0' => 0,
                'kfloat' => 0.12345,
                'kfalse' => false,
                'ktrue' => true,
            ],
        ]));

        // Arrayable
        $arrayable = new class implements Arrayable {
            public function toArray(): array
            {
                return [
                    'kstring' => 'string',
                    'kint1' => 1,
                    'kint0' => 0,
                    'kfloat' => 0.12345,
                    'kfalse' => false,
                    'ktrue' => true,
                    'karray' => [
                        'kstring' => 'string',
                        'kint1' => 1,
                        'kint0' => 0,
                        'kfloat' => 0.12345,
                        'kfalse' => false,
                        'ktrue' => true,
                    ],
                ];
            }
        };
        $this->assertSame($expected, $reflectionMethod->invoke($response, $arrayable));

        // Xmlable
        $xmlable = new class($expected) implements Xmlable {
            private $result;

            public function __construct($result)
            {
                $this->result = $result;
            }

            public function __toString(): string
            {
                return $this->result;
            }
        };
        $this->assertSame($expected, $reflectionMethod->invoke($response, $xmlable));
    }

    public function testToJson()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $psrResponse = new \Hyperf\HttpMessage\Base\Response();
        Context::set(PsrResponseInterface::class, $psrResponse);

        $response = new Response();
        $json = $response->json([
            'kstring' => 'string',
            'kint1' => 1,
            'kint0' => 0,
            'kfloat' => 0.12345,
            'kfalse' => false,
            'ktrue' => true,
            'karray' => [
                'kstring' => 'string',
                'kint1' => 1,
                'kint0' => 0,
                'kfloat' => 0.12345,
                'kfalse' => false,
                'ktrue' => true,
            ],
        ]);

        $this->assertSame('{"kstring":"string","kint1":1,"kint0":0,"kfloat":0.12345,"kfalse":false,"ktrue":true,"karray":{"kstring":"string","kint1":1,"kint0":0,"kfloat":0.12345,"kfalse":false,"ktrue":true}}', (string) $json->getBody());
    }

    public function testHtml()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $psrResponse = new \Hyperf\HttpMessage\Base\Response();
        Context::set(PsrResponseInterface::class, $psrResponse);

        $response = new Response();
        $html = $response->html('<h1>hello world</h1>');
        $this->assertSame('<h1>hello world</h1>', (string) $html->getBody());

        $html = $response->html('<h1>hello world</h1>', 'GBK');
        $this->assertSame('<h1>hello world</h1>', (string) $html->getBody());
        $this->assertSame('text/html; charset=GBK', $html->getHeaderLine('content-type'));
    }

    public function testObjectToJson()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $psrResponse = new \Hyperf\HttpMessage\Base\Response();
        Context::set(PsrResponseInterface::class, $psrResponse);

        $response = new Response();
        $json = $response->json((object) ['id' => 1, 'name' => 'Hyperf']);

        $this->assertSame('{"id":1,"name":"Hyperf"}', (string) $json->getBody());
    }

    public function testPsrResponse()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $psrResponse = new \Hyperf\HttpMessage\Base\Response();
        Context::set(PsrResponseInterface::class, $psrResponse);

        $response = new Response();
        $response = $response->withBody(new SwooleStream('xxx'));

        $this->assertInstanceOf(PsrResponseInterface::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testCookiesAndHeaders()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $swooleResponse = Mockery::mock(SwooleResponse::class);
        $id = uniqid();
        $cookie1 = new Cookie('Name', 'Hyperf');
        $cookie2 = new Cookie('Request-Id', $id);
        $swooleResponse->shouldReceive('status')->with(200, 'OK')->andReturnUsing(function ($code) {
            $this->assertSame($code, 200);
            return true;
        });
        $swooleResponse->shouldReceive('header')->withAnyArgs()->twice()->andReturnUsing(function ($name, $value) {
            if ($name == 'X-Token') {
                $this->assertSame($value, ['xxx']);
            }
            return true;
        });
        $swooleResponse->shouldReceive('rawcookie')->withAnyArgs()->twice()->andReturnUsing(function ($name, $value, ...$args) use ($id) {
            $this->assertTrue($name == 'Name' || $name == 'Request-Id');
            $this->assertTrue($value == 'Hyperf' || $value == $id);
            return true;
        });
        $swooleResponse->shouldReceive('end')->once()->andReturn(true);

        Context::set(PsrResponseInterface::class, $psrResponse = new \Hyperf\HttpMessage\Server\Response());

        $response = new Response();
        $response = $response->withCookie($cookie1)->withCookie($cookie2)->withHeader('X-Token', 'xxx')->withStatus(200);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $response->raw('Hello Hyperf.');
        $this->assertNotInstanceOf(Response::class, $response);
        $this->assertNotInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(PsrResponseInterface::class, $response);

        $responseEmitter = new ResponseEmitter(null);
        $responseEmitter->emit($response, $swooleResponse, true);

        $this->assertSame($psrResponse, Context::get(PsrResponseInterface::class));
    }
}
