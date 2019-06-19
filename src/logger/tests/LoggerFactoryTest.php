<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Logger;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @covers \Hyperf\Logger\LoggerFactory
 */
class LoggerFactoryTest extends TestCase
{
    public function testInvokeLoggerFactory()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);
        $this->assertInstanceOf(LoggerFactory::class, $factory);
    }

    public function testInvokeLoggerFromFactory()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);

        $logger = $factory->get('hyperf');
        $this->assertInstanceOf(StdoutLoggerInterface::class, $logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertInstanceOf(\Hyperf\Logger\Logger::class, $logger);
    }

    private function mockContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'logger' => [
                'default' => [
                    'handler' => [
                        'class' => \Monolog\Handler\StreamHandler::class,
                        'constructor' => [
                            'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                            'level' => \Monolog\Logger::DEBUG,
                        ],
                    ],
                    'formatter' => [
                        'class' => \Monolog\Formatter\LineFormatter::class,
                        'constructor' => [],
                    ],
                ],
            ],
        ]));

        $container->shouldReceive('get')
            ->once()
            ->with(LoggerFactory::class)
            ->andReturn(new LoggerFactory($container));

        return $container;
    }
}
