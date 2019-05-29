<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\ConfigAliyunAcm;

use Hyperf\Config\Config;
use Hyperf\ConfigAliyunAcm\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
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
        $container = Mockery::mock(ContainerInterface::class);
        $configInstance = new Config([]);
        $configInstance->set('aliyun_acm.addressServer', 'pre-value');
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($configInstance);
        ApplicationContext::setContainer($container);
        $client = new Client(function () use ($container) {
            return (new ClientFactory($container))->create();
        });
        $client->pull();
        $config = $container->get(ConfigInterface::class);
        $this->assertSame('after-value', $config->get('aliyun_acm.test-key'));
        $this->assertSame([
            'test-key' => 'after-value',
        ], $config->get('aliyun_acm'));
    }
}
