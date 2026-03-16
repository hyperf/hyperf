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

namespace HyperfTest\RpcMultiplex\Cases;

use Hyperf\Rpc\Context;
use Hyperf\RpcMultiplex\Contract\HostReaderInterface;
use Hyperf\RpcMultiplex\HttpMessage\HostReader\NullHostReader;
use Hyperf\RpcMultiplex\HttpMessageBuilder;
use Hyperf\RpcMultiplex\Packer\JsonPacker;
use Hyperf\Support\Reflection\ClassInvoker;
use PHPUnit\Framework\Attributes\CoversNothing;
use Psr\Http\Message\UriInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class HttpMessageBuilderTest extends AbstractTestCase
{
    public function testBuildUri()
    {
        $invoker = new ClassInvoker(new HttpMessageBuilder(new JsonPacker(), new Context(), new NullHostReader()));
        /** @var UriInterface $uri */
        $uri = $invoker->buildUri('/', $host = uniqid(), 8806);
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('/', $uri->getPath());
        $this->assertSame($host, $uri->getHost());
        $this->assertSame(8806, $uri->getPort());
    }

    public function testBuildRequestWithHostReader()
    {
        $invoker = new ClassInvoker(new HttpMessageBuilder(new JsonPacker(), new Context(), new class implements HostReaderInterface {
            public function read(): string
            {
                return 'test_case';
            }
        }));
        /** @var ServerRequestPlusInterface $request */
        $request = $invoker->buildRequest(['path' => '/hi', 'extra' => ['from' => 'test']], ['port' => 9502]);
        $uri = $request->getUri();

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('/hi', $uri->getPath());
        $this->assertSame('test_case', $uri->getHost());
        $this->assertSame(9502, $uri->getPort());

        $this->assertSame('test', $request->getHeaderLine('from'));
    }

    public function testStoreContext()
    {
        $builder = new HttpMessageBuilder(new JsonPacker(), $context = new Context(), new NullHostReader());

        $request = $builder->buildRequest([
            'path' => '/',
            'data' => ['id' => 1],
        ]);

        $this->assertSame(['id' => 1], $request->getParsedBody());
        $this->assertSame([], $context->getData());

        $request = $builder->buildRequest([
            'path' => '/',
            'data' => ['id' => 1],
            'context' => ['trace_id' => $id = uniqid()],
        ]);

        $this->assertSame(['id' => 1], $request->getParsedBody());
        $this->assertSame(['trace_id' => $id], $context->getData());
    }
}
