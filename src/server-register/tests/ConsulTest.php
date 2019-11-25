<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\ServerRegister;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Server\Server;
use Hyperf\ServerRegister\Agent\ConsulAgent;
use Hyperf\ServerRegister\Listener\RegisterServerListener;
use Hyperf\ServerRegister\ServerHelper;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ConsulTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testServerRegister()
    {
        $container = $this->getContainer();

        $listener = new RegisterServerListener($container);
        $listener->process(new MainWorkerStart(null, 0));

        $client = $container->get(ConsulAgent::class);
        $services = $client->services();

        $service = null;
        foreach ($services as $item) {
            if ($item->getService() === 'server_register.http-server') {
                $service = $item;
            }
        }

        $this->assertNotNull($service);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with('Meta')->andReturn($meta = [
            'ID' => uniqid(),
        ]);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config = new Config([
            'server' => [
                'servers' => [
                    [
                        'name' => 'http',
                        'type' => Server::SERVER_HTTP,
                        'host' => '0.0.0.0',
                        'port' => 10001,
                    ],
                ],
            ],
            'server_register' => [
                'enable' => true,
                'agent' => \Hyperf\ServerRegister\Agent\ConsulAgent::class,
                'servers' => [
                    [
                        'server' => 'http',
                        'name' => 'server_register.http-server',
                        'meta' => [
                            // Consul Check Params
                            'check' => [
                                'DeregisterCriticalServiceAfter' => '60s',
                                'Interval' => '1s',
                            ],
                            // Consul Meta
                            'meta' => $meta,
                        ],
                    ],
                ],
            ],
        ]));
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug', 'info')->andReturn(null);
            return $logger;
        });
        $container->shouldReceive('get')->with(ConsulAgent::class)->andReturnUsing(function () use ($container) {
            return new ConsulAgent($container->get(ConfigInterface::class), new ClientFactory($container));
        });
        $container->shouldReceive('get')->with(ServerHelper::class)->andReturn(new ServerHelper($config));

        return $container;
    }
}
