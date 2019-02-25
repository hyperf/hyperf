<?php

namespace HyperfTest\ConfigApollo;


use Hyperf\Config\Config;
use Hyperf\ConfigApollo\Client;
use Hyperf\ConfigApollo\Option;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ClientTest extends TestCase
{
    public function testPull()
    {
        $option = new Option();
        $option->setServer('http://127.0.0.1:8080')->setAppid('test')->setCluster('default')->setClientIp('127.0.0.1');
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([]));
        ApplicationContext::setContainer($container);
        $callbacks = [
            'application' => function ($configs) {
                $container = ApplicationContext::getContainer();
                $config  = $container->get(ConfigInterface::class);
                foreach ($configs['configurations'] ?? [] as $key => $value) {
                    $config->set($key, $value);
                }
            },
        ];
        $client = new Client($option, $callbacks, function (array $options = []) use ($container) {
            return (new ClientFactory($container))->create($options);
        });
        $client->pull([
            'application',
        ]);
        $config = $container->get(ConfigInterface::class);
        $this->assertSame('test-value', $config->get('test-key'));
    }


}