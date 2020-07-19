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
namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Server\Request\JsonParser;
use Hyperf\HttpMessage\Server\Request\Parser;
use Hyperf\HttpMessage\Server\Request\XmlParser;
use Hyperf\HttpMessage\Server\RequestParserInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Codec\Xml;
use HyperfTest\HttpMessage\Stub\ParserStub;
use HyperfTest\HttpMessage\Stub\Server\RequestStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Swoole\Http\Request as SwooleRequest;

/**
 * @internal
 * @coversNothing
 */
class ServerRequestTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        RequestStub::setParser(null);
    }

    public function testNormalizeParsedBody()
    {
        $this->getContainer();

        $data = ['id' => 1];
        $json = ['name' => 'Hyperf'];

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('');

        $this->assertSame($data, RequestStub::normalizeParsedBody($data));
        $this->assertSame($data, RequestStub::normalizeParsedBody($data, $request));

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/xml; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(Xml::toXml($json)));

        $this->assertSame($json, RequestStub::normalizeParsedBody($json, $request));

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(Json::encode($json)));
        $this->assertSame($json, RequestStub::normalizeParsedBody($data, $request));
    }

    /**
     * @expectedException  \Hyperf\HttpMessage\Exception\BadRequestHttpException
     */
    public function testNormalizeParsedBodyException()
    {
        $this->getContainer();

        $json = ['name' => 'Hyperf'];
        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream('xxxx'));
        $this->assertSame([], RequestStub::normalizeParsedBody($json, $request));
    }

    /**
     * @expectedException  \Hyperf\HttpMessage\Exception\BadRequestHttpException
     */
    public function testXmlNormalizeParsedBodyException()
    {
        $this->getContainer();

        $json = ['name' => 'Hyperf'];
        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/xml; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream('xxxx'));
        $this->assertSame([], RequestStub::normalizeParsedBody($json, $request));
    }

    public function testNormalizeEmptyBody()
    {
        $this->getContainer();

        $json = ['name' => 'Hyperf'];
        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(''));
        $this->assertSame($json, RequestStub::normalizeParsedBody($json, $request));

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(''));
        $this->assertSame([], RequestStub::normalizeParsedBody([], $request));
    }

    public function testNormalizeParsedBodyInvalidContentType()
    {
        $this->getContainer();

        $data = ['id' => 1];
        $json = ['name' => 'Hyperf'];

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/JSON');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(json_encode($json)));
        $this->assertSame($json, RequestStub::normalizeParsedBody($data, $request));
    }

    public function testOverrideRequestParser()
    {
        $this->getContainer();
        $this->assertSame(Parser::class, get_class(RequestStub::getParser()));

        RequestStub::setParser(new ParserStub());
        $json = ['name' => 'Hyperf'];

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/JSON');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(json_encode($json)));
        $this->assertSame(['mock' => true], RequestStub::normalizeParsedBody([], $request));
        $this->assertSame(ParserStub::class, get_class(RequestStub::getParser()));
    }

    public function testGetUriFromGlobals()
    {
        $swooleRequest = Mockery::mock(SwooleRequest::class);
        $data = ['name' => 'Hyperf'];
        $swooleRequest->shouldReceive('rawContent')->andReturn(Json::encode($data));
        $swooleRequest->server = ['server_port' => 9501];
        $request = Request::loadFromSwooleRequest($swooleRequest);
        $uri = $request->getUri();
        $this->assertSame(9501, $uri->getPort());

        $swooleRequest = Mockery::mock(SwooleRequest::class);
        $data = ['name' => 'Hyperf'];
        $swooleRequest->shouldReceive('rawContent')->andReturn(Json::encode($data));
        $swooleRequest->header = ['host' => '127.0.0.1'];
        $swooleRequest->server = ['server_port' => 9501];
        $request = Request::loadFromSwooleRequest($swooleRequest);
        $uri = $request->getUri();
        $this->assertSame(null, $uri->getPort());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('make')->with(JsonParser::class, Mockery::any())->andReturn(new JsonParser());
        $container->shouldReceive('make')->with(XmlParser::class, Mockery::any())->andReturn(new XmlParser());

        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(RequestParserInterface::class)->andReturn(new Parser());

        return $container;
    }
}
