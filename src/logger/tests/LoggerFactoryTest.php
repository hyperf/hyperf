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
namespace HyperfTest\Logger;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use HyperfTest\Logger\Stub\BarProcessor;
use HyperfTest\Logger\Stub\FooHandler;
use HyperfTest\Logger\Stub\FooProcessor;
use Mockery;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @internal
 * @covers \Hyperf\Logger\LoggerFactory
 */
class LoggerFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set('test.logger.foo_handler.record', null);
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

    public function testHandlerGroupNotWorks()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf');
        $this->assertInstanceOf(\Hyperf\Logger\Logger::class, $logger);
        $reflectionClass = new ReflectionClass($logger);
        $handlersProperty = $reflectionClass->getProperty('handlers');
        $handlersProperty->setAccessible(true);
        $handlers = $handlersProperty->getValue($logger);
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);

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

    public function testNotSetProcessor()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf');
        $reflectionClass = new ReflectionClass($logger);
        $handlersProperty = $reflectionClass->getProperty('processors');
        $handlersProperty->setAccessible(true);
        $processors = $handlersProperty->getValue($logger);
        $this->assertSame([], $processors);
    }

    public function testProcessor()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf', 'processor-test');
        $reflectionClass = new ReflectionClass($logger);
        $handlersProperty = $reflectionClass->getProperty('processors');
        $handlersProperty->setAccessible(true);
        $processors = $handlersProperty->getValue($logger);
        $this->assertSame(3, count($processors));
        $this->assertInstanceOf(FooProcessor::class, $processors[0]);

        $logger->info('Hello world.');

        $this->assertSame(
            'Hello world.Hello world.',
            Context::get('test.logger.foo_handler.record')['extra']['message']
        );
        $this->assertTrue(Context::get('test.logger.foo_handler.record')['extra']['bar']);
        $this->assertTrue(Context::get('test.logger.foo_handler.record')['extra']['callback']);
    }

    public function testDefaultProcessor()
    {
        $container = $this->mockContainer();
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf', 'default-processor');
        $reflectionClass = new ReflectionClass($logger);
        $handlersProperty = $reflectionClass->getProperty('processors');
        $handlersProperty->setAccessible(true);
        $processors = $handlersProperty->getValue($logger);
        $this->assertSame(1, count($processors));
        $this->assertInstanceOf(FooProcessor::class, $processors[0]);

        $logger->info('Hello world.');

        $this->assertSame(
            'Hello world.Hello world.',
            Context::get('test.logger.foo_handler.record')['extra']['message']
        );
    }

    private function mockContainer(): ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config = new Config([
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
                            'formatter' => [
                                'class' => \Monolog\Formatter\LineFormatter::class,
                            ],
                        ],
                        [
                            'class' => \Monolog\Handler\TestHandler::class,
                            'constructor' => [
                                'level' => \Monolog\Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => \Monolog\Formatter\LineFormatter::class,
                            ],
                        ],
                    ],
                    'formatter' => [
                        'class' => \Monolog\Formatter\LineFormatter::class,
                        'constructor' => [],
                    ],
                ],
                'processor-test' => [
                    'handlers' => [
                        [
                            'class' => FooHandler::class,
                            'constructor' => [
                                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                                'level' => \Monolog\Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => \Monolog\Formatter\LineFormatter::class,
                            ],
                        ],
                    ],
                    'processors' => [
                        [
                            'class' => FooProcessor::class,
                            'constructor' => [
                                'repeat' => 2,
                            ],
                        ],
                        [
                            'class' => BarProcessor::class,
                        ],
                        function (array|LogRecord $records) {
                            $records['extra']['callback'] = true;
                            return $records;
                        },
                    ],
                ],
                'default-processor' => [
                    'handlers' => [
                        [
                            'class' => FooHandler::class,
                            'constructor' => [
                                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                                'level' => \Monolog\Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => \Monolog\Formatter\LineFormatter::class,
                            ],
                        ],
                    ],
                    'processor' => [
                        'class' => FooProcessor::class,
                        'constructor' => [
                            'repeat' => 2,
                        ],
                    ],
                ],
            ],
        ]));

        $container->shouldReceive('get')
            ->with(LoggerFactory::class)
            ->andReturn(new LoggerFactory($container, $config));

        return $container;
    }
}
