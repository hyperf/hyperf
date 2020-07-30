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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\Logger;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @covers \Hyperf\Logger\Logger
 */
class LoggerTest extends TestCase
{
    public function testInstanceOfMonoLogger()
    {
        $logger = \Mockery::mock(Logger::class);

        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
    }

    public function testInstanceOfLoggerInterface()
    {
        $logger = \Mockery::mock(Logger::class);

        $this->assertInstanceOf(StdoutLoggerInterface::class, $logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testLogThrowable()
    {
        $logger = new Logger('test', [
            $handler = new TestHandler(),
        ]);

        $logger->error(new \RuntimeException('Invalid Arguments'));

        $this->assertRegExp('/RuntimeException: Invalid Arguments/', $handler->getRecords()[0]['message']);
    }
}
