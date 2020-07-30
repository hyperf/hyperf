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
namespace HyperfTest\GrpcClient;

use Grpc\UserReply;
use Hyperf\Di\Container;
use Hyperf\Grpc\Parser;
use Hyperf\GrpcClient\BaseClient;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\ChannelPool;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Parallel;
use HyperfTest\GrpcClient\Stub\HiClient;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Http\Server;
use TypeError;
use function GuzzleHttp\Promise\coroutine;

/**
 * @internal
 * @coversNothing
 */
class BaseClientTest extends TestCase
{
    public static $server;

    public static function setUpBeforeClass()
    {
        // Dummy server pretending as gRPC
        Coroutine::create(function () {
            self::$server = new Server('127.0.0.1', 2222, false);
            self::$server->handle('/', function ($request, $response) {
                $response->end(Parser::serializeMessage(new UserReply()));
            });
            self::$server->handle('/bug', function ($request, $response) {
                $response->end(false);
            });
            self::$server->start();
        });
    }

    public static function tearDownAfterClass()
    {
        Coroutine::create(function () {
            self::$server->shutdown();
        });
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGrpcClientConnectionFailure()
    {
        $this->getContainer();
        $client = new BaseClient('127.0.0.1:1111');
        $this->expectException(GrpcClientException::class);
        $client->_getGrpcClient();
    }

    public function testGrpcClientLaziness()
    {
        $this->getContainer();
        $client = new BaseClient('127.0.0.1:2222');
        $this->assertTrue(true); // No Exception Occurs
        $this->assertNotNull($client->_getGrpcClient());
    }

    public function testGrpcClientAutoClose()
    {
        $this->getContainer();
        $client = new BaseClient('127.0.0.1:2222');
        $this->assertTrue($client->isConnected());
        $grpcClient = $client->_getGrpcClient();
        unset($client);
        $this->assertFalse($grpcClient->isConnected());
    }

    public function testGrpcClientReconnect()
    {
        $this->getContainer();
        $client = new HiClient('127.0.0.1:2222');
        $this->assertGreaterThan(0, $client->sayHello());
        $client->close();
        $this->assertGreaterThan(0, $client->sayHello());
    }

    public function testGrpcClientConcurrent()
    {
        $this->getContainer();

        $p = new Parallel();
        $p->add(function () {
            $client = new HiClient('127.0.0.1:2222', ['retry_attempts' => 0]);
            $this->assertGreaterThan(0, $client->sayHello());
            $this->assertGreaterThan(0, $client->sayHello());
            $this->assertGreaterThan(0, $client->sayHello());
            $this->assertGreaterThan(0, $client->sayHello());
            return true;
        });
        $p->add(function () {
            $client = new HiClient('127.0.0.1:2222', ['retry_attempts' => 0]);
            $this->assertGreaterThan(0, $client->sayHello());
            $this->assertGreaterThan(0, $client->sayHello());
            $this->assertGreaterThan(0, $client->sayHello());
            $this->assertGreaterThan(0, $client->sayHello());
            return true;
        });
        $p->wait(true);
    }

    public function testGrpcClientWithBuggyServer()
    {
        $this->getContainer();
        $client = new HiClient('127.0.0.1:2222', ['retry_attempts' => 0]);
        try {
            $client->sayBug();
        } catch (TypeError $e) {
            $this->assertNotNull($e);
        } finally {
            $this->assertGreaterThan(0, $client->sayHello());
            $this->assertGreaterThan(0, $client->sayHello());
            $this->assertGreaterThan(0, $client->sayHello());
        }
    }

    public function getContainer()
    {
        $container = \Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ChannelPool::class)->andReturn(new ChannelPool());
        $container->shouldReceive('has')->andReturn(false);
        ApplicationContext::setContainer($container);
        return $container;
    }
}
