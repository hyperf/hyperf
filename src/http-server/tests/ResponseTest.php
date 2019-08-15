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

namespace HyperfTest\HttpServer;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Xmlable;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @internal
 * @coversNothing
 */
class ResponseTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        Context::set(PsrResponseInterface::class, null);
    }

    public function testRedirect()
    {
        $container = Mockery::mock(ContainerInterface::class);
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
    }

    public function testToXml()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $psrResponse = new \Hyperf\HttpMessage\Base\Response();
        Context::set(PsrResponseInterface::class, $psrResponse);

        $response = new Response();
        $reflectionClass = new \ReflectionClass(Response::class);
        $reflectionMethod = $reflectionClass->getMethod('toXml');
        $reflectionMethod->setAccessible(true);

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
        $arrayable = new class() implements Arrayable {
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

        $this->assertSame('{"kstring":"string","kint1":1,"kint0":0,"kfloat":0.12345,"kfalse":false,"ktrue":true,"karray":{"kstring":"string","kint1":1,"kint0":0,"kfloat":0.12345,"kfalse":false,"ktrue":true}}', $json->getBody()->getContents());
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
    }
}
