<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Logger;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\Logger;
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
}
