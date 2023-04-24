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

use Hyperf\Codec\Packer\JsonPacker;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\JsonRpc\DataFormatter;
use Hyperf\JsonRpc\JsonRpcTransporter;
use Hyperf\JsonRpc\NormalizeDataFormatter;
use Hyperf\JsonRpc\PathGenerator;
use Hyperf\Logger\Logger;
use Hyperf\Rpc\IdGenerator\IdGeneratorInterface;
use Hyperf\Rpc\IdGenerator\UniqidIdGenerator;
use Hyperf\RpcClient\Exception\RequestException;
use Hyperf\RpcClient\ProxyFactory;
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\SymfonyNormalizer;
use HyperfTest\JsonRpc\Stub\CalculatorProxyServiceClient;
use HyperfTest\JsonRpc\Stub\CalculatorServiceInterface;
use HyperfTest\JsonRpc\Stub\IntegerValue;
use Mockery;
use Mockery\MockInterface;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 * @coversNothing
 */
class RpcServiceClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testServiceClient()
    {
        $container = $this->createContainer();

        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $transporter->shouldReceive('send')
            ->andReturnUsing(function ($data) {
                $id = json_decode($data, true)['id'];
                return json_encode([
                    'id' => $id,
                    'result' => 3,
                ]);
            });
        $service = new CalculatorProxyServiceClient($container, CalculatorServiceInterface::class, 'jsonrpc');
        $ret = $service->add(1, 2);
        $this->assertEquals(3, $ret);
    }

    public function testServiceClientReturnArray()
    {
        $container = $this->createContainer();

        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $transporter->shouldReceive('send')
            ->andReturnUsing(function ($data) {
                $id = json_decode($data, true)['id'];
                return json_encode([
                    'id' => $id,
                    'result' => ['params' => [1, 2], 'sum' => 3],
                ]);
            });
        $service = new CalculatorProxyServiceClient($container, CalculatorServiceInterface::class, 'jsonrpc');
        $ret = $service->array(1, 2);
        $this->assertEquals(['params' => [1, 2], 'sum' => 3], $ret);
    }

    public function testServiceClientReturnNull()
    {
        $container = $this->createContainer();

        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $transporter->shouldReceive('send')
            ->andReturnUsing(function ($data) {
                $id = json_decode($data, true)['id'];
                return json_encode([
                    'id' => $id,
                    'result' => null,
                ]);
            });
        $service = new CalculatorProxyServiceClient($container, CalculatorServiceInterface::class, 'jsonrpc');
        $ret = $service->null();
        $this->assertNull($ret);
    }

    public function testProxyFactory()
    {
        $container = $this->createContainer();
        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $transporter->shouldReceive('send')
            ->andReturnUsing(function ($data) {
                $id = json_decode($data, true)['id'];
                return json_encode([
                    'id' => $id,
                    'result' => 3,
                ]);
            });
        $factory = new ProxyFactory();
        $proxyClass = $factory->createProxy(CalculatorServiceInterface::class);
        /** @var CalculatorServiceInterface $service */
        $service = new $proxyClass($container, CalculatorServiceInterface::class, 'jsonrpc');
        $ret = $service->add(1, 2);
        $this->assertEquals(3, $ret);
    }

    public function testProxyReturnNullableType()
    {
        $container = $this->createContainer();
        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $uniqid = uniqid();
        $transporter->shouldReceive('send')
            ->andReturnUsing(function ($data) use ($uniqid) {
                $id = json_decode($data, true)['id'];
                return json_encode([
                    'id' => $id,
                    'result' => $uniqid,
                ]);
            });
        $factory = new ProxyFactory();
        $proxyClass = $factory->createProxy(CalculatorServiceInterface::class);
        /** @var CalculatorServiceInterface $service */
        $service = new $proxyClass($container, CalculatorServiceInterface::class, 'jsonrpc');
        $ret = $service->getString();
        $this->assertEquals($uniqid, $ret);
    }

    public function testProxyReturnNullableTypeWithNull()
    {
        $container = $this->createContainer();
        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $transporter->shouldReceive('send')
            ->andReturnUsing(function ($data) {
                $id = json_decode($data, true)['id'];
                return json_encode([
                    'id' => $id,
                    'result' => null,
                ]);
            });
        $factory = new ProxyFactory();
        $proxyClass = $factory->createProxy(CalculatorServiceInterface::class);
        /** @var CalculatorServiceInterface $service */
        $service = new $proxyClass($container, CalculatorServiceInterface::class, 'jsonrpc');
        $ret = $service->getString();
        $this->assertNull($ret);
    }

    public function testProxyCallableParameterAndReturnArray()
    {
        $container = $this->createContainer();
        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $transporter->shouldReceive('send')
            ->andReturnUsing(function ($data) {
                $data = json_decode($data, true);
                return json_encode([
                    'id' => $data['id'],
                    'result' => $data['params'],
                ]);
            });
        $factory = new ProxyFactory();
        $proxyClass = $factory->createProxy(CalculatorServiceInterface::class);
        /** @var CalculatorServiceInterface $service */
        $service = new $proxyClass($container, CalculatorServiceInterface::class, 'jsonrpc');
        $ret = $service->callable(function () {
        }, null);
        $this->assertEquals([[], null], $ret);
    }

    public function testProxyFactoryWithErrorId()
    {
        $container = $this->createContainer();
        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $transporter->shouldReceive('send')
            ->andReturn(json_encode([
                'id' => '1234',
                'result' => 3,
            ]));
        $once = true;
        $transporter->shouldReceive('recv')->andReturnUsing(function () use (&$once) {
            $this->assertTrue($once);
            $once = false;
            return json_encode([
                'id' => '1234',
                'result' => 3,
            ]);
        });
        $factory = new ProxyFactory();
        $proxyClass = $factory->createProxy(CalculatorServiceInterface::class);
        /** @var CalculatorServiceInterface $service */
        $service = new $proxyClass($container, CalculatorServiceInterface::class, 'jsonrpc');

        $this->expectException(RequestException::class);
        $this->expectExceptionMessageMatches('/^Invalid response\. Request id\[.*\] is not equal to response id\[1234\]\.$/');
        $service->add(1, 2);
    }

    public function testProxyFactoryObjectParameter()
    {
        $container = $this->createContainer();
        /** @var MockInterface $transporter */
        $transporter = $container->get(JsonRpcTransporter::class);
        $transporter->shouldReceive('setLoadBalancer')
            ->andReturnSelf();
        $transporter->shouldReceive('send')
            ->andReturnUsing(function ($data) {
                $id = json_decode($data, true)['id'];
                return json_encode([
                    'id' => $id,
                    'result' => ['value' => 3],
                ]);
            });
        $factory = new ProxyFactory();
        $proxyClass = $factory->createProxy(CalculatorServiceInterface::class);
        /** @var CalculatorServiceInterface $service */
        $service = new $proxyClass($container, CalculatorServiceInterface::class, 'jsonrpc');
        $ret = $service->sum(IntegerValue::newInstance(1), IntegerValue::newInstance(2));
        $this->assertInstanceOf(IntegerValue::class, $ret);
        $this->assertEquals(3, $ret->getValue());
    }

    public function createContainer()
    {
        $transporter = Mockery::mock(JsonRpcTransporter::class);
        $container = new Container(new DefinitionSource([
            NormalizerInterface::class => SymfonyNormalizer::class,
            Serializer::class => SerializerFactory::class,
            DataFormatter::class => NormalizeDataFormatter::class,
            MethodDefinitionCollectorInterface::class => MethodDefinitionCollector::class,
            StdoutLoggerInterface::class => function () {
                return new Logger('App', [new StreamHandler('php://stderr')]);
            },
            ConfigInterface::class => function () {
                return new Config([
                    'services' => [
                        'consumers' => [
                            [
                                'name' => CalculatorServiceInterface::class,
                                'nodes' => [
                                    ['host' => '0.0.0.0', 'port' => 1234],
                                ],
                            ],
                        ],
                    ],
                    'protocols' => [
                        'jsonrpc' => [
                            'packer' => JsonPacker::class,
                            'transporter' => JsonRpcTransporter::class,
                            'path-generator' => PathGenerator::class,
                            'data-formatter' => DataFormatter::class,
                        ],
                    ],
                ]);
            },
            JsonRpcTransporter::class => function () use ($transporter) {
                return $transporter;
            },
            IdGeneratorInterface::class => UniqidIdGenerator::class,
        ]));
        ApplicationContext::setContainer($container);
        return $container;
    }
}
