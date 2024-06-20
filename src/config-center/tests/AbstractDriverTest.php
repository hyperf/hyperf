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
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AbstractDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        CoordinatorManager::clear(Constants::WORKER_EXIT);
    }

    #[DataProvider('getConfig')]
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

    #[DataProvider('getConfigAndPipeMessage')]
    public function testOnPipeMessage(Config $config, PipeMessageInterface $pipeMessage, array $assert)
    {
        ContainerStub::mockContainer($config);
        $driver = $config->get('config_center.driver', '');
        $factory = new DriverFactory($config);
        $driver = $factory->create($driver);

        $driver->onPipeMessage($pipeMessage);
        $this->assertSame($assert, $config->get('test'));
    }

    public static function getConfig(): array
    {
        return [
            [self::getEtcdConfig()],
            [self::getNacosConfig()],
        ];
    }

    public static function getConfigAndPipeMessage()
    {
        $assert = ['message' => 'Hello Hyperf', 'id' => 1];
        return [
            [
                self::getEtcdConfig(),
                self::getEtcdPipeMessage(),
                $assert,
            ],
            [
                self::getNacosConfig(),
                self::getNacosPipeMessage(),
                $assert,
            ],
            [
                self::getNacosConfig(ConfigNacos\Constants::CONFIG_MERGE_APPEND),
                self::getNacosPipeMessage(),
                array_merge(['name' => 'Hyperf'], $assert),
            ],
        ];
    }

    protected static function getEtcdPipeMessage(): PipeMessage
    {
        return new PipeMessage([
            '/application/test' => [
                'key' => '/application/test',
                'value' => Json::encode(['message' => 'Hello Hyperf', 'id' => 1]),
            ],
        ]);
    }

    protected static function getNacosPipeMessage(): PipeMessage
    {
        return new PipeMessage([
            'test' => ['message' => 'Hello Hyperf', 'id' => 1],
        ]);
    }

    protected static function getNacosConfig($mergeMode = ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE): Config
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

    protected static function getEtcdConfig(): Config
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
