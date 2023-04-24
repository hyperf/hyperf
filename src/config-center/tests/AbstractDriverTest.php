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
namespace HyperfTest\ConfigCenter;

use Hyperf\Codec\Json;
use Hyperf\Config\Config;
use Hyperf\ConfigCenter\Contract\PipeMessageInterface;
use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\ConfigCenter\Mode;
use Hyperf\ConfigCenter\PipeMessage;
use Hyperf\ConfigEtcd\EtcdDriver;
use Hyperf\ConfigNacos;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AbstractDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        CoordinatorManager::clear(Constants::WORKER_EXIT);
    }

    /**
     * @dataProvider getConfig
     */
    public function testCreateMessageFetcherLoopForCoroutineMode(Config $config)
    {
        ContainerStub::mockContainer($config);
        $driver = $config->get('config_center.driver', '');
        $factory = new DriverFactory($config);
        $driver = $factory->create($driver);

        $driver->createMessageFetcherLoop();
        sleep(2);
        $this->assertSame(['message' => 'Hello Hyperf', 'id' => 1], $config->get('test'));
    }

    /**
     * @dataProvider getConfigAndPipeMessage
     */
    public function testOnPipeMessage(Config $config, PipeMessageInterface $pipeMessage, array $assert)
    {
        ContainerStub::mockContainer($config);
        $driver = $config->get('config_center.driver', '');
        $factory = new DriverFactory($config);
        $driver = $factory->create($driver);

        $driver->onPipeMessage($pipeMessage);
        $this->assertSame($assert, $config->get('test'));
    }

    public function getConfig(): array
    {
        return [
            [$this->getEtcdConfig()],
            [$this->getNacosConfig()],
        ];
    }

    public function getConfigAndPipeMessage()
    {
        $assert = ['message' => 'Hello Hyperf', 'id' => 1];
        return [
            [
                $this->getEtcdConfig(),
                $this->getEtcdPipeMessage(),
                $assert,
            ],
            [
                $this->getNacosConfig(),
                $this->getNacosPipeMessage(),
                $assert,
            ],
            [
                $this->getNacosConfig(ConfigNacos\Constants::CONFIG_MERGE_APPEND),
                $this->getNacosPipeMessage(),
                array_merge(['name' => 'Hyperf'], $assert),
            ],
        ];
    }

    protected function getEtcdPipeMessage(): PipeMessage
    {
        return new PipeMessage([
            '/application/test' => [
                'key' => '/application/test',
                'value' => Json::encode(['message' => 'Hello Hyperf', 'id' => 1]),
            ],
        ]);
    }

    protected function getNacosPipeMessage(): PipeMessage
    {
        return new PipeMessage([
            'test' => ['message' => 'Hello Hyperf', 'id' => 1],
        ]);
    }

    protected function getNacosConfig($mergeMode = ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE): Config
    {
        return new Config([
            'config_center' => [
                'enable' => true,
                'mode' => Mode::COROUTINE,
                'driver' => 'nacos',
                'drivers' => [
                    'nacos' => [
                        'driver' => ConfigNacos\NacosDriver::class,
                        'merge_mode' => $mergeMode,
                        'interval' => 1,
                        'default_key' => 'nacos_config',
                    ],
                ],
            ],
            'test' => ['name' => 'Hyperf'],
        ]);
    }

    protected function getEtcdConfig(): Config
    {
        return new Config([
            'config_center' => [
                'enable' => true,
                'mode' => Mode::COROUTINE,
                'driver' => 'etcd',
                'drivers' => [
                    'etcd' => [
                        'driver' => EtcdDriver::class,
                        'interval' => 1,
                        'namespaces' => [
                            '/application',
                        ],
                        'mapping' => [
                            '/application/test' => 'test',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
