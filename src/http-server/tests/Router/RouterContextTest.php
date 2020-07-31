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
namespace HyperfTest\HttpServer\Router;

use FastRoute\Dispatcher;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\HttpServer\Router\RouterContext;
use Hyperf\Utils\Context;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
class RouterContextTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
    }

    public function testGetRouteName()
    {
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')->with(Dispatched::class)->andReturnUsing(function () {
            return new Dispatched([
                Dispatcher::FOUND,
                new Handler([], '/', ['name' => 'index']),
                [
                    'id' => uniqid(),
                ],
            ]);
        });
        Context::set(ServerRequestInterface::class, $request);
        $context = new RouterContext();
        $this->assertSame('index', $context->getRouteName());
    }
}
