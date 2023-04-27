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
use Hyperf\RpcMultiplex\HttpMessageBuilder;
use Hyperf\RpcMultiplex\Packer\JsonPacker;
use Hyperf\Support\Reflection\ClassInvoker;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 * @coversNothing
 */
class HttpMessageBuilderTest extends AbstractTestCase
{
    public function testBuildUri()
    {
        $invoker = new ClassInvoker(new HttpMessageBuilder(new JsonPacker(), new Context()));
        /** @var UriInterface $uri */
        $uri = $invoker->buildUri('/', $host = uniqid(), 8806);
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('/', $uri->getPath());
        $this->assertSame($host, $uri->getHost());
        $this->assertSame(8806, $uri->getPort());
    }

    public function testStoreContext()
    {
        $builder = new HttpMessageBuilder(new JsonPacker(), $context = new Context());

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
