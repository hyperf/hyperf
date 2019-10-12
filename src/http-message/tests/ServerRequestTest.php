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

namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Server\Request\JsonParser;
use Hyperf\HttpMessage\Server\Request\Parser;
use Hyperf\HttpMessage\Server\Request\XmlParser;
use Hyperf\HttpMessage\Server\RequestParserInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Json;
use Hyperf\Utils\Xml;
use HyperfTest\HttpMessage\Stub\Server\RequestStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 * @coversNothing
 */
class ServerRequestTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testNormalizeParsedBody()
    {
        $this->getContainer();

        $data = ['id' => 1];
        $json = ['name' => 'Hyperf'];

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('');

        $this->assertSame($data, RequestStub::normalizeParsedBody($data));
        $this->assertSame($data, RequestStub::normalizeParsedBody($data, $request));

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/xml; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(Xml::toXml($json)));

        $this->assertSame($json, RequestStub::normalizeParsedBody($json, $request));

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(Json::encode($json)));
        $this->assertSame($json, RequestStub::normalizeParsedBody($data, $request));
    }

    /**
     * @expectedException  \Hyperf\HttpMessage\Exception\BadRequestHttpException
     * @expectedExceptionMessage Invalid JSON data in request body: Syntax error.
     */
    public function testNormalizeParsedBodyException()
    {
        $this->getContainer();

        $json = ['name' => 'Hyperf'];
        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream('xxxx'));
        $this->assertSame([], RequestStub::normalizeParsedBody($json, $request));
    }

    public function testNormalizeParsedBodyInvalidContentType()
    {
        $this->getContainer();

        $data = ['id' => 1];
        $json = ['name' => 'Hyperf'];

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/JSON');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(json_encode($json)));
        $this->assertSame($json, RequestStub::normalizeParsedBody($data, $request));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(RequestParserInterface::class)->andReturn(new Parser());
        $container->shouldReceive('get')->with(JsonParser::class)->andReturn(new JsonParser());
        $container->shouldReceive('get')->with(XmlParser::class)->andReturn(new XmlParser());

        ApplicationContext::setContainer($container);

        return $container;
    }
}
