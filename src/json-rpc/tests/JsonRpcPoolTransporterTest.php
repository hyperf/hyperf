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
namespace HyperfTest\JsonRpc;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Engine\Contract\Socket\SocketFactoryInterface;
use Hyperf\Engine\Socket\SocketFactory;
use Hyperf\JsonRpc\Exception\ClientException;
use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\Packer\JsonLengthPacker;
use Hyperf\JsonRpc\Pool\Frequency;
use Hyperf\JsonRpc\Pool\PoolFactory;
use Hyperf\JsonRpc\Pool\RpcPool;
use Hyperf\LoadBalancer\Node;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use HyperfTest\JsonRpc\Stub\RpcPoolStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class JsonRpcPoolTransporterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testJsonRpcPoolTransporterConfig()
    {
        $factory = Mockery::mock(PoolFactory::class);
        $transporter = new JsonRpcPoolTransporter($factory, [
            'pool' => ['min_connections' => 10],
            'settings' => $settings = [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
            ],
        ]);

        $this->assertSame(10, $transporter->getConfig()['pool']['min_connections']);
        $this->assertSame(32, $transporter->getConfig()['pool']['max_connections']);
        $this->assertSame($settings, $transporter->getConfig()['settings']);
    }

    public function testJsonRpcPoolTransporterGetPool()
    {
        $container = $this->getContainer();
        $factory = new PoolFactory($container);
        $transporter = new JsonRpcPoolTransporter($factory, [
            'pool' => ['min_connections' => 8, 'max_connections' => 88],
            'settings' => $settings = [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
            ],
        ]);

        $options = $transporter->getPool()->getOption();
        $this->assertSame(8, $options->getMinConnections());
        $this->assertSame(88, $options->getMaxConnections());
    }

    public function testJsonRpcPoolTransporterSendLengthCheck()
    {
        $container = $this->getContainer();
        $factory = $container->get(PoolFactory::class);
        $transporter = new JsonRpcPoolTransporter($factory, $options = [
            'pool' => ['min_connections' => 10],
            'settings' => [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
            ],
        ]);
        $transporter->setNodes([new Node('127.0.0.1', 9504)]);

        $packer = new JsonLengthPacker($options);

        $string = $transporter->send($packer->pack($data = ['id' => $id = uniqid()]));

        $this->assertSame($data, $packer->unpack($string));
    }

    public function testJsonRpcPoolTransporterSendEofCheck()
    {
        $container = $this->getContainer();
        $factory = $container->get(PoolFactory::class);
        $transporter = new JsonRpcPoolTransporter($factory, $options = [
            'pool' => ['min_connections' => 10],
            'settings' => [
                'open_eof_split' => true,
                'package_eof' => "\r\n",
            ],
        ]);
        $transporter->setNodes([new Node('127.0.0.1', 9504)]);

        $packer = new JsonLengthPacker($options);

        $string = $transporter->send($packer->pack($data = ['id' => $id = uniqid()]));

        $this->assertSame($data, $packer->unpack($string));
    }

    public function testGetConnection()
    {
        $container = $this->getContainer();
        $factory = $container->get(PoolFactory::class);
        $transporter = new JsonRpcPoolTransporter($factory, [
            'pool' => ['min_connections' => 10],
            'settings' => $settings = [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
            ],
        ]);

        $conn = $transporter->getConnection();
        $conn2 = $transporter->getConnection();
        $this->assertSame($conn, $conn2);
        $conn->close();
        $conn2 = $transporter->getConnection();
        $this->assertSame($conn, $conn2);
        $conn->close();
        $conn->reconnectCallback = static function () {
            throw new ClientException();
        };
        $conn2 = $transporter->getConnection();
        $this->assertNotEquals($conn, $conn2);
    }

    public function testsplObjectHash()
    {
        $class = new stdClass();
        $class->id = 1;
        $hash = spl_object_hash($class);

        $class->id = 2;
        $hash2 = spl_object_hash($class);

        $this->assertSame($hash, $hash2);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('get')->with(PoolFactory::class)->andReturn(new PoolFactory($container));
        $container->shouldReceive('make')->with(RpcPool::class, Mockery::any())->andReturnUsing(function ($_, $args) use ($container) {
            return new RpcPoolStub($container, $args['name'], $args['config']);
        });
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn(null);
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new Channel(10);
        });
        $container->shouldReceive('get')->with(SocketFactoryInterface::class)->andReturn(new SocketFactory());

        return $container;
    }
}
