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

namespace HyperfTest\ConfigApollo;

use GuzzleHttp;
use Hyperf\Codec\Json;
use Hyperf\Config\Config;
use Hyperf\ConfigApollo\ApolloDriver;
use Hyperf\ConfigApollo\Client;
use Hyperf\ConfigApollo\ClientInterface;
use Hyperf\ConfigApollo\Option;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Support\Reflection\ClassInvoker;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ClientTest extends TestCase
{
    public function testPull()
    {
        $option = new Option();
        $option->setServer('http://127.0.0.1:8080')->setAppid('test')->setCluster('default')->setClientIp('127.0.0.1');
        $container = Mockery::mock(ContainerInterface::class);
        $configInstance = new Config([]);
        $configInstance->set('apollo.test-key', 'pre-value');
        $configInstance->set('config_center.drivers.apollo.namespaces', ['application']);
        // drivers.apollo.namespaces
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($configInstance);
        ApplicationContext::setContainer($container);

        $config = $container->get(ConfigInterface::class);
        $client = new Client(
            $option,
            function (array $options = []) {
                $client = Mockery::mock(GuzzleHttp\Client::class);
                $response = (new Response())->setStatus(200)->addHeader('content-type', 'application/json')
                    ->setBody(new SwooleStream(Json::encode([
                        'configurations' => [
                            'apollo.test-key' => 'after-value',
                        ],
                    ])));
                $client->shouldReceive('get')->andReturn($response);
                return $client;
            },
            $config,
            $logger = Mockery::mock(StdoutLoggerInterface::class)
        );
        $res = $client->pull();
        $this->assertSame(['application' => ['apollo.test-key' => 'after-value']], $res);

        $container->shouldReceive('get')->with(ClientInterface::class)->andReturn($client);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn($logger);
        $logger->shouldReceive('debug')->withAnyArgs()->andReturnNull();
        $driver = new ApolloDriver($container);
        $driver->fetchConfig();

        $this->assertSame(['test-key' => 'after-value'], $config->get('apollo'));
    }

    public function testConfigurationsChanged()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([]));
        $container->shouldReceive('get')->with(ClientInterface::class)->andReturn(Mockery::mock(ClientInterface::class));
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(Mockery::mock(StdoutLoggerInterface::class));

        $driver = (new ClassInvoker(new ApolloDriver($container)));
        $prevConfig = ['a' => ['id' => 1], 'b' => ['id' => 2]];
        ksort($prevConfig);

        $this->assertFalse($driver->configChanged(['a' => ['id' => 1], 'b' => ['id' => 2]], $prevConfig));
        $this->assertFalse($driver->configChanged(['a' => ['id' => 1], 'b' => ['id' => 2]], $prevConfig));
    }
}
