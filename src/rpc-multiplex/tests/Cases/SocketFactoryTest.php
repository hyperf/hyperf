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

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\LoadBalancer\Random;
use Hyperf\RpcMultiplex\Socket;
use Hyperf\RpcMultiplex\SocketFactory;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\RpcMultiplex\Stub\ContainerStub;
use Mockery;

use function Hyperf\Coroutine\go;

/**
 * @internal
 * @coversNothing
 */
class SocketFactoryTest extends AbstractTestCase
{
    public function testSocketConfig()
    {
        $container = ContainerStub::mockContainer();

        $factory = new SocketFactory($container, [
            'connect_timeout' => $connectTimeout = rand(5, 10),
            'settings' => [
                'package_max_length' => $lenght = rand(1000, 9999),
            ],
            'recv_timeout' => $recvTimeout = rand(5, 10),
            'retry_count' => 2,
            'retry_interval' => 100,
            'client_count' => 4,
        ]);

        $balancer = Mockery::mock(LoadBalancerInterface::class);
        $balancer->shouldReceive('isAutoRefresh')->andReturnFalse();
        $factory->setLoadBalancer($balancer);
        $balancer->shouldReceive('getNodes')->andReturn([
            new Node('192.168.0.1', 9501),
            new Node('192.168.0.2', 9501),
        ]);

        $factory->refresh();

        $this->assertSame($connectTimeout, (new ClassInvoker($factory))->config['connect_timeout']);

        $clients = (new ClassInvoker($factory))->clients;
        $this->assertSame(4, count($clients));

        /** @var Socket $client */
        $client = $clients[0];
        $invoker = new ClassInvoker($client);
        $this->assertSame(9501, $invoker->port);
        $this->assertSame($lenght, $invoker->config['package_max_length']);
        $this->assertSame($connectTimeout, $invoker->config['connect_timeout']);
        $this->assertSame($recvTimeout, $invoker->config['recv_timeout']);
    }

    public function testLoadBalancerNodeRefreshedButDontChanged()
    {
        $container = ContainerStub::mockContainer();

        $factory = new SocketFactory($container, [
            'client_count' => 2,
        ]);

        $balancer = new Random();
        $balancer->setNodes([
            new Node('192.168.0.1', 9501),
            new Node('192.168.0.2', 9501),
        ]);
        $balancer->refresh(function () {
            return [
                new Node('192.168.0.1', 9501),
                new Node('192.168.0.2', 9501),
            ];
        }, 100);
        $factory->setLoadBalancer($balancer);

        $factory->refresh();

        $clients = (new ClassInvoker($factory))->clients;
        /** @var Socket $client */
        foreach ($clients as $client) {
            $client = (new ClassInvoker($client));
            $this->assertTrue(in_array($client->name, ['192.168.0.1', '192.168.0.2']));
        }

        sleep(1);

        /** @var Socket $client */
        foreach ($clients as $client) {
            $client = (new ClassInvoker($client));
            $this->assertTrue(in_array($client->name, ['192.168.0.1', '192.168.0.2']));
        }

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        CoordinatorManager::clear(Constants::WORKER_EXIT);
        $balancer->clearAfterRefreshedCallbacks();
    }

    public function testLoadBalancerNodeRefreshed()
    {
        $container = ContainerStub::mockContainer();

        $factory = new SocketFactory($container, [
            'client_count' => 2,
        ]);

        $balancer = new Random();
        $balancer->setNodes([
            new Node('192.168.0.1', 9501),
            new Node('192.168.0.2', 9501),
        ]);
        $balancer->refresh(function () {
            return [
                new Node('192.168.0.2', 9501),
                new Node('192.168.0.3', 9501),
            ];
        }, 100);
        $factory->setLoadBalancer($balancer);

        $factory->refresh();

        $clients = (new ClassInvoker($factory))->clients;
        /** @var Socket $client */
        foreach ($clients as $client) {
            $client = (new ClassInvoker($client));
            $this->assertTrue(in_array($client->name, ['192.168.0.1', '192.168.0.2']));
        }

        sleep(1);

        /** @var Socket $client */
        foreach ($clients as $client) {
            $client = (new ClassInvoker($client));
            $this->assertTrue(in_array($client->name, ['192.168.0.2', '192.168.0.3']));
        }

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        CoordinatorManager::clear(Constants::WORKER_EXIT);
        $balancer->clearAfterRefreshedCallbacks();
    }

    public function testSocketRefreshInMoreThanOneCoroutine()
    {
        $container = ContainerStub::mockContainer();

        $factory = new SocketFactory($container, [
            'connect_timeout' => $connectTimeout = rand(5, 10),
            'settings' => [
                'package_max_length' => $lenght = rand(1000, 9999),
            ],
            'recv_timeout' => $recvTimeout = rand(5, 10),
            'retry_count' => 2,
            'retry_interval' => 100,
            'client_count' => 4,
        ]);

        go(function () use ($factory) {
            $balancer = Mockery::mock(LoadBalancerInterface::class);
            $balancer->shouldReceive('isAutoRefresh')->andReturnFalse();
            $factory->setLoadBalancer($balancer);
            $balancer->shouldReceive('getNodes')->andReturn([
                new Node('192.168.0.1', 9501),
                new Node('192.168.0.2', 9501),
            ]);

            $factory->refresh();
        });

        $class = new ClassInvoker($factory);
        $this->assertSame(4, count($class->clients));
    }
}
