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

use Hyperf\Codec\Json;
use Hyperf\Codec\Xml;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Server\Request\JsonParser;
use Hyperf\HttpMessage\Server\Request\Parser;
use Hyperf\HttpMessage\Server\Request\XmlParser;
use Hyperf\HttpMessage\Server\RequestParserInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use HyperfTest\HttpMessage\Stub\ParserStub;
use HyperfTest\HttpMessage\Stub\Server\RequestStub;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use ReflectionClass;
use Swoole\Http\Request as SwooleRequest;

/**
 * @internal
 * @coversNothing
 */
class ServerRequestTest extends TestCase
{
    protected function tearDown(): void
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

    public function testNormalizeParsedBodyException()
    {
        $this->expectException(\Hyperf\HttpMessage\Exception\BadRequestHttpException::class);

        $this->getContainer();

        $json = ['name' => 'Hyperf'];
        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('content-type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream('xxxx'));
        $this->assertSame([], RequestStub::normalizeParsedBody($json, $request));
    }

    public function testXmlNormalizeParsedBodyException()
    {
        $this->expectException(\Hyperf\HttpMessage\Exception\BadRequestHttpException::class);

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

    /**
     * @group ParseHost
     */
    public function testParseHost()
    {
        $hostStrIPv4 = '192.168.119.100:9501';
        $hostStrIPv6 = '[fe80::a464:1aff:fe88:7b5a]:9502';
        $objReflectClass = new ReflectionClass('Hyperf\HttpMessage\Server\Request');
        $method = $objReflectClass->getMethod('parseHost');
        $method->setAccessible(true);

        $resIPv4 = $method->invokeArgs(null, [$hostStrIPv4]);
        $this->assertSame('192.168.119.100', $resIPv4[0]);
        $this->assertSame(9501, $resIPv4[1]);

        $resIPv6 = $method->invokeArgs(null, [$hostStrIPv6]);
        $this->assertSame('[fe80::a464:1aff:fe88:7b5a]', $resIPv6[0]);
        $this->assertSame(9502, $resIPv6[1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid host: ');
        $method->invokeArgs(null, ['']);
    }

    /**
     * @dataProvider  getIPv6Examples
     * @param mixed $originHost
     * @param mixed $host
     * @param mixed $port
     */
    public function testGetUriFromGlobalsForIPv6Host($originHost, $host, $port)
    {
        $swooleRequest = Mockery::mock(SwooleRequest::class);
        $data = ['name' => 'Hyperf'];
        $swooleRequest->shouldReceive('rawContent')->andReturn(Json::encode($data));

        $swooleRequest->server = [
            'http_host' => $originHost,
        ];
        $request = Request::loadFromSwooleRequest($swooleRequest);
        $uri = $request->getUri();
        $this->assertSame($port, $uri->getPort());
        $this->assertSame($host, $uri->getHost());

        $swooleRequest->server = [];
        $swooleRequest->header = [
            'host' => $originHost,
        ];
        $request = Request::loadFromSwooleRequest($swooleRequest);
        $uri = $request->getUri();
        $this->assertSame($port, $uri->getPort());
        $this->assertSame($host, $uri->getHost());
    }

    public function getIPv6Examples(): array
    {
        return [
            ['localhost:9501', 'localhost', 9501],
            ['localhost:', 'localhost', null],
            ['localhost', 'localhost', null],
            ['[2a00:f48:1008::212:183:10]', '[2a00:f48:1008::212:183:10]', null],
            ['[2a00:f48:1008::212:183:10]:9501', '[2a00:f48:1008::212:183:10]', 9501],
            ['[2a00:f48:1008::212:183:10]:', '[2a00:f48:1008::212:183:10]', null],
        ];
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
