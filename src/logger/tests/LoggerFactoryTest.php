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
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @internal
 * @covers \Hyperf\Logger\LoggerFactory
 */
class LoggerFactoryTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testInvokeLoggerFactory()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);
        $this->assertInstanceOf(LoggerFactory::class, $factory);
    }

    public function testInvokeLoggerFromFactory()
    {
        $container = $this->mockContainer();
        ApplicationContext::setContainer($container);
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf');
        $this->assertInstanceOf(\Hyperf\Logger\Logger::class, $logger);
    }

    public function testHandlerConfig()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf', 'default');
        $this->assertInstanceOf(\Hyperf\Logger\Logger::class, $logger);
        $reflectionClass = new ReflectionClass($logger);
        $handlersProperty = $reflectionClass->getProperty('handlers');
        $handlersProperty->setAccessible(true);
        $handlers = $handlersProperty->getValue($logger);
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(StreamHandler::class, current($handlers));
    }

    public function testHandlersConfig()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf', 'default-handlers');
        $this->assertInstanceOf(\Hyperf\Logger\Logger::class, $logger);
        $reflectionClass = new ReflectionClass($logger);
        $handlersProperty = $reflectionClass->getProperty('handlers');
        $handlersProperty->setAccessible(true);
        $handlers = $handlersProperty->getValue($logger);
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);
        $this->assertInstanceOf(TestHandler::class, $handlers[1]);
    }

    private function mockContainer(): ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
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
                'default-handlers' => [
                    'handlers' => [
                        [
                            'class' => \Monolog\Handler\StreamHandler::class,
                            'constructor' => [
                                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                                'level' => \Monolog\Logger::DEBUG,
                            ],
                        ],
                        [
                            'class' => \Monolog\Handler\TestHandler::class,
                            'constructor' => [
                                'level' => \Monolog\Logger::DEBUG,
                            ],
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
            ->with(LoggerFactory::class)
            ->andReturn(new LoggerFactory($container));

        return $container;
    }
}
