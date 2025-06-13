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
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\Logger\Stub\BarProcessor;
use HyperfTest\Logger\Stub\FooHandler;
use HyperfTest\Logger\Stub\FooProcessor;
use Mockery;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversClass(LoggerFactory::class)]
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

    public function testInvokeLoggerByCallableConfigFromFactory()
    {
        $container = $this->mockContainer();
        ApplicationContext::setContainer($container);
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get($name = uniqid(), 'callable');

        $this->assertStringContainsString($name, (new ClassInvoker((new ClassInvoker($logger))->handlers[0]))->url);
    }

    public function testInvokeLoggerFromFactoryByString()
    {
        $container = $this->mockContainer();
        ApplicationContext::setContainer($container);
        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get(group: 'string');
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
        $handlers = $handlersProperty->getValue($logger);
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);

        $logger = $factory->get('hyperf', 'default-handlers');
        $this->assertInstanceOf(\Hyperf\Logger\Logger::class, $logger);
        $reflectionClass = new ReflectionClass($logger);
        $handlersProperty = $reflectionClass->getProperty('handlers');
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
                        'class' => StreamHandler::class,
                        'constructor' => [
                            'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                            'level' => Logger::DEBUG,
                        ],
                    ],
                    'formatter' => [
                        'class' => LineFormatter::class,
                        'constructor' => [],
                    ],
                ],
                'callable' => fn (string $name) => [
                    'handler' => [
                        'class' => StreamHandler::class,
                        'constructor' => [
                            'stream' => BASE_PATH . '/runtime/logs/' . $name . '.log',
                            'level' => Logger::DEBUG,
                        ],
                    ],
                ],
                'string' => ['handlers' => ['default']],
                'default-handlers' => [
                    'handlers' => [
                        [
                            'class' => StreamHandler::class,
                            'constructor' => [
                                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                                'level' => Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => LineFormatter::class,
                            ],
                        ],
                        [
                            'class' => TestHandler::class,
                            'constructor' => [
                                'level' => Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => LineFormatter::class,
                            ],
                        ],
                    ],
                    'formatter' => [
                        'class' => LineFormatter::class,
                        'constructor' => [],
                    ],
                ],
                'processor-test' => [
                    'handlers' => [
                        [
                            'class' => FooHandler::class,
                            'constructor' => [
                                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                                'level' => Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => LineFormatter::class,
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
                                'level' => Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => LineFormatter::class,
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
