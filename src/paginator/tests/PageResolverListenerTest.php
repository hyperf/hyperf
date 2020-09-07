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
namespace HyperfTest\Paginator;

use Hyperf\Di\Container;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Request;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Paginator\Listener\PageResolverListener;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
class PageResolverListenerTest extends TestCase
{
    protected function setUp()
    {
        Context::set(PsrServerRequestInterface::class, null);
        Context::set('http.request.parsedData', null);
    }

    protected function tearDown()
    {
        Mockery::close();
        Context::set(PsrServerRequestInterface::class, null);
        Context::set('http.request.parsedData', null);
    }

    public function testPageResolve()
    {
        $this->getContainer();
        $paginator = new LengthAwarePaginator([1, 2], 10, 2, null);
        $this->assertSame('/?page=2', $paginator->nextPageUrl());

        $listener = new PageResolverListener();
        $listener->process(new BootApplication());

        $paginator = new LengthAwarePaginator([1, 2], 10, 2, null);
        $this->assertSame('/?page=2', $paginator->nextPageUrl());

        Context::set(PsrServerRequestInterface::class, value(function () {
            $request = new \Hyperf\HttpMessage\Server\Request('GET', '/index');
            return $request->withQueryParams(['page' => 2]);
        }));
        $paginator = new LengthAwarePaginator([1, 2], 10, 2, null);
        $this->assertSame('/?page=3', $paginator->nextPageUrl());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(RequestInterface::class)->andReturn(new Request());

        ApplicationContext::setContainer($container);
        return $container;
    }
}
