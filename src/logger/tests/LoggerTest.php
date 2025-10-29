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
use Mockery;
use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
#[CoversClass(Logger::class)]
class LoggerTest extends TestCase
{
    public function testInstanceOfMonoLogger()
    {
        $logger = Mockery::mock(Logger::class);

        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
    }

    public function testInstanceOfLoggerInterface()
    {
        $logger = Mockery::mock(Logger::class);

        $this->assertInstanceOf(StdoutLoggerInterface::class, $logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testLogThrowable()
    {
        $logger = new Logger('test', [
            $handler = new TestHandler(),
        ]);

        $logger->error(new RuntimeException('Invalid Arguments'));

        $this->assertMatchesRegularExpression('/RuntimeException: Invalid Arguments/', $handler->getRecords()[0]['message']);
    }

    public function testLoggingLoopDetection()
    {
        $logger = new Logger('test', [
            $handler = new class extends TestHandler {
                protected function write(array|LogRecord $record): void
                {
                    usleep(1);
                    parent::write($record);
                }
            },
        ]);

        $callbacks = [];
        for ($i = 0; $i < 4; ++$i) {
            $callbacks[] = static function () use ($logger) {
                $logger->info('Hello World.');
            };
        }

        parallel($callbacks);

        $messages = array_column($handler->getRecords(), 'message');
        foreach ($messages as $message) {
            $this->assertSame('Hello World.', $message);
        }
    }
}
