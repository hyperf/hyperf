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
use Mockery;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class LoggerConfigTest extends TestCase
{
    public function testDefaultHandler()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'logger' => [
                'default' => [
                ],
            ],
        ]));

        $container->shouldReceive('get')
            ->with(LoggerFactory::class)
            ->andReturn(new LoggerFactory($container));

        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf');

        $handlers = $logger->getHandlers();

        $this->assertSame(1, count($handlers));
        $handler = $handlers[0];
        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertInstanceOf(LineFormatter::class, $handler->getFormatter());
    }

    public function testDefaultFormatter()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'logger' => [
                'default' => [
                    'formatter' => [
                        'class' => HtmlFormatter::class,
                    ],
                ],
            ],
        ]));

        $container->shouldReceive('get')
            ->with(LoggerFactory::class)
            ->andReturn(new LoggerFactory($container));

        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf');

        $handlers = $logger->getHandlers();

        $this->assertSame(1, count($handlers));
        $handler = $handlers[0];
        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());
    }

    public function testHandlers()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'logger' => [
                'default' => [
                    'handlers' => [
                        [
                            'class' => StreamHandler::class,
                            'constructor' => [
                                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                                'level' => Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => LineFormatter::class,
                                'constructor' => [],
                            ],
                        ],
                        [
                            'class' => RotatingFileHandler::class,
                            'constructor' => [
                                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                                'level' => Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => HtmlFormatter::class,
                                'constructor' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]));

        $container->shouldReceive('get')
            ->with(LoggerFactory::class)
            ->andReturn(new LoggerFactory($container));

        $factory = $container->get(LoggerFactory::class);
        $logger = $factory->get('hyperf');

        $handlers = $logger->getHandlers();

        $this->assertSame(2, count($handlers));
        $handler = $handlers[0];
        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertInstanceOf(LineFormatter::class, $handler->getFormatter());
        $handler = $handlers[1];
        $this->assertInstanceOf(RotatingFileHandler::class, $handler);
        $this->assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());
    }
}
