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

use Hyperf\Config\Config;
use Hyperf\ConfigApollo\Client;
use Hyperf\ConfigApollo\Option;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    public function testPull()
    {
        $option = new Option();
        $option->setServer('http://127.0.0.1:8080')->setAppid('test')->setCluster('default')->setClientIp('127.0.0.1');
        $container = Mockery::mock(ContainerInterface::class);
        $configInstance = new Config([]);
        $configInstance->set('apollo.test-key', 'pre-value');
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($configInstance);
        ApplicationContext::setContainer($container);
        $callbacks = [
            'application' => function ($configs) {
                $container = ApplicationContext::getContainer();
                $config = $container->get(ConfigInterface::class);
                // Mock the configurations.
                $configs['configurations'] = [
                    'apollo.test-key' => 'after-value',
                ];
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
        $this->assertSame('after-value', $config->get('apollo.test-key'));
        $this->assertSame([
            'test-key' => 'after-value',
        ], $config->get('apollo'));
    }
}
