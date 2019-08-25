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

namespace HyperfTest\GrpcServer;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\GrpcServer\StatusCode;
use Hyperf\HttpMessage\Server\Response;
use HyperfTest\GrpcServer\Stub\GrpcExceptionHandlerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class GrpcExceptionHandlerTest extends TestCase
{
    public function testTransferToResponse200()
    {
        $container = $this->getContainer();

        $logger = $container->get(StdoutLoggerInterface::class);
        $formatter = $container->get(FormatterInterface::class);
        $swooleResponse = Mockery::mock(\Swoole\Http\Response::class);
        $data = [];
        $swooleResponse->shouldReceive('trailer')->andReturnUsing(function (...$args) use (&$data) {
            $data[] = $args;
        });
        $response = new Response($swooleResponse);
        $handler = new GrpcExceptionHandlerStub($logger, $formatter);
        $response = $handler->transferToResponse(StatusCode::OK, 'OK', $response);
        $this->assertSame([['grpc-status', '0'], ['grpc-message', 'OK']], $data);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testTransferToResponse499()
    {
        $container = $this->getContainer();

        $logger = $container->get(StdoutLoggerInterface::class);
        $formatter = $container->get(FormatterInterface::class);
        $swooleResponse = Mockery::mock(\Swoole\Http\Response::class);
        $data = [];
        $swooleResponse->shouldReceive('trailer')->andReturnUsing(function (...$args) use (&$data) {
            $data[] = $args;
        });
        $response = new Response($swooleResponse);
        $handler = new GrpcExceptionHandlerStub($logger, $formatter);
        $response = $handler->transferToResponse(StatusCode::CANCELLED, 'The operation was cancelled', $response);
        $this->assertSame([['grpc-status', '1'], ['grpc-message', 'The operation was cancelled']], $data);
        $this->assertSame(499, $response->getStatusCode());
    }

    public function testTransferToResponseUnKnown()
    {
        $container = $this->getContainer();

        $logger = $container->get(StdoutLoggerInterface::class);
        $formatter = $container->get(FormatterInterface::class);
        $swooleResponse = Mockery::mock(\Swoole\Http\Response::class);
        $data = [];
        $swooleResponse->shouldReceive('trailer')->andReturnUsing(function (...$args) use (&$data) {
            $data[] = $args;
        });
        $response = new Response($swooleResponse);
        $handler = new GrpcExceptionHandlerStub($logger, $formatter);
        $response = $handler->transferToResponse(123, 'UNKNOWN', $response);
        $this->assertSame([['grpc-status', '123'], ['grpc-message', 'UNKNOWN']], $data);
        $this->assertSame(500, $response->getStatusCode());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive(Mockery::any())->with(Mockery::any())->andReturn(null);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn($logger);

        $formatter = Mockery::mock(FormatterInterface::class);
        $formatter->shouldReceive(Mockery::any())->with(Mockery::any())->andReturn('');
        $container->shouldReceive('get')->with(FormatterInterface::class)->andReturn($formatter);

        return $container;
    }
}
