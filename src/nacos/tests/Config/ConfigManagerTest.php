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
namespace HyperfTest\Nacos\Config;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Client;
use Hyperf\Nacos\Config\ConfigManager;
use HyperfTest\Nacos\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConfigManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConfigManagerMerge()
    {
        $container = ContainerStub::getContainer();

        $client = new Client($container);

        $data = $client->pull();

        $container->get(ConfigManager::class)->merge($data);

        $config = $container->get(ConfigInterface::class);
        $res = $config->get('nacos_config');

        $this->assertSame(['id' => 1, 'data' => 'Hello World'], $res);
        $this->assertSame(['ids' => [1, 2, 3]], $config->get('nacos_default_config'));
    }
}
