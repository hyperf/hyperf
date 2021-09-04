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

use Hyperf\RpcMultiplex\HttpMessageBuilder;
use Hyperf\RpcMultiplex\Packer\JsonPacker;
use Hyperf\Utils\Reflection\ClassInvoker;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 * @coversNothing
 */
class HttpMessageBuilderTest extends AbstractTestCase
{
    public function testBuildUri()
    {
        $invoker = new ClassInvoker(new HttpMessageBuilder(new JsonPacker()));
        /** @var UriInterface $uri */
        $uri = $invoker->buildUri('/', $host = uniqid(), 8806);
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('/', $uri->getPath());
        $this->assertSame($host, $uri->getHost());
        $this->assertSame(8806, $uri->getPort());
    }
}
