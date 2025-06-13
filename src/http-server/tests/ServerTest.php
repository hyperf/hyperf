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
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Exception\BadRequestHttpException;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\ResponseEmitter;
use Hyperf\Support\SafeCaller;
use HyperfTest\HttpServer\Stub\ServerStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ServerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        CoordinatorManager::clear(Constants::WORKER_START);
    }

    public function testThrowExceptionInCatchOnRequest()
    {
        CoordinatorManager::until(Constants::WORKER_START)->resume();
        $container = $this->getContainer();
        $dispatcher = Mockery::mock(ExceptionHandlerDispatcher::class);
        $emitter = Mockery::mock(ResponseEmitter::class);
        $server = Mockery::mock(ServerStub::class . '[initRequestAndResponse]', [
            $container,
            Mockery::mock(HttpDispatcher::class),
            $dispatcher,
            $emitter,
        ]);

        $dispatcher->shouldReceive('dispatch')->andReturnUsing(function ($exception) {
            throw new RuntimeException('Fatal Error');
        });

        $emitter->shouldReceive('emit')->once()->andReturnUsing(function ($response) {
            $this->assertInstanceOf(Psr7Response::class, $response);
            $this->assertSame(400, $response->getStatusCode());
        });

        $server->shouldReceive('initRequestAndResponse')->andReturnUsing(function () {
            // Initialize PSR-7 Request and Response objects.
            throw new BadRequestHttpException();
        });

        $server->onRequest($req = Mockery::mock(Request::class), $res = Mockery::mock(Response::class));
    }

    public function testOnRequest()
    {
        CoordinatorManager::until(Constants::WORKER_START)->resume();
        $container = $this->getContainer();
        $dispatcher = Mockery::mock(ExceptionHandlerDispatcher::class);
        $emitter = Mockery::mock(ResponseEmitter::class);
        $server = Mockery::mock(ServerStub::class . '[initRequestAndResponse]', [
            $container,
            Mockery::mock(HttpDispatcher::class),
            $dispatcher,
            $emitter,
        ]);

        $dispatcher->shouldReceive('dispatch')->andReturnUsing(function ($exception) {
            if ($exception instanceof HttpException) {
                return (new Psr7Response())->withStatus($exception->getStatusCode());
            }
            return null;
        });

        $emitter->shouldReceive('emit')->once()->andReturnUsing(function ($response) {
            $this->assertInstanceOf(Psr7Response::class, $response);
            $this->assertSame(400, $response->getStatusCode());
        });

        $server->shouldReceive('initRequestAndResponse')->andReturnUsing(function () {
            // Initialize PSR-7 Request and Response objects.
            throw new BadRequestHttpException();
        });

        $server->onRequest($req = Mockery::mock(Request::class), $res = Mockery::mock(Response::class));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('get')->with(SafeCaller::class)->andReturn(new SafeCaller($container));

        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')->andReturn(true);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($dispatcher);

        ApplicationContext::setContainer($container);

        return $container;
    }
}
