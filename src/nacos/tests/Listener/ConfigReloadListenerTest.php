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
namespace HyperfTest\Nacos\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Client;
use Hyperf\Nacos\Config\PipeMessage;
use Hyperf\Nacos\Listener\ConfigReloadListener;
use HyperfTest\Nacos\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConfigReloadListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConfigReloadProcess()
    {
        $container = ContainerStub::getContainer();

        $client = new Client($container);

        $data = $client->pull();

        $listener = new ConfigReloadListener($container);

        $listener->process(new \Hyperf\Process\Event\PipeMessage(new PipeMessage($data)));

        $config = $container->get(ConfigInterface::class);
        $res = $config->get('nacos_config');

        $this->assertSame(['id' => 1, 'data' => 'Hello World'], $res);
        $this->assertSame(['ids' => [1, 2, 3]], $config->get('nacos_default_config'));
    }
}
