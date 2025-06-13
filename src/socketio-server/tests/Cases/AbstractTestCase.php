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

namespace HyperfTest\SocketIOServer\Cases;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Framework\Logger\StdoutLogger;
use Hyperf\SocketIOServer\Atomic;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\Room\MemoryAdapter;
use Hyperf\SocketIOServer\Room\MemoryRoom;
use Hyperf\SocketIOServer\Room\RoomInterface;
use Hyperf\SocketIOServer\SidProvider\LocalSidProvider;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\SocketIOServer\SocketIO;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\Timer;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Timer::clearAll();
    }

    protected function getContainer()
    {
        ! defined('BASE_PATH') && define('BASE_PATH', '.');
        $container = new Container(new DefinitionSource([]));
        $container->define(StdoutLoggerInterface::class, StdoutLogger::class);
        $container->define(RoomInterface::class, MemoryRoom::class);
        $container->define(AdapterInterface::class, MemoryAdapter::class);
        $container->define(SidProviderInterface::class, LocalSidProvider::class);
        $container->define(ConfigInterface::class, Config::class);
        $container->define(Encoder::class, Encoder::class);
        $container->set(ConfigInterface::class, new Config([]));
        SocketIO::$messageId = new Atomic();
        ApplicationContext::setContainer($container);
        return $container;
    }
}
