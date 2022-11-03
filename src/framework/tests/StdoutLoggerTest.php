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
namespace HyperfTest\Framework;

use Hyperf\Config\Config;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Logger\StdoutLogger;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @internal
 * @coversNothing
 */
class StdoutLoggerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testLog()
    {
        $output = Mockery::mock(ConsoleOutput::class);
        $output->shouldReceive('writeln')->with(Mockery::any())->once()->andReturnUsing(function ($message) {
            $this->assertSame('<info>[INFO]</> Hello Hyperf.', $message);
        });
        $logger = new StdoutLogger(new Config([
            StdoutLoggerInterface::class => [
                'log_level' => [
                    LogLevel::INFO,
                ],
            ],
        ]), $output);

        $logger->info('Hello {name}.', ['name' => 'Hyperf']);
    }

    public function testLogThrowable()
    {
        $output = Mockery::mock(ConsoleOutput::class);
        $output->shouldReceive('writeln')->with(Mockery::any())->once()->andReturnUsing(function ($message) {
            $this->assertMatchesRegularExpression('/RuntimeException: Invalid Arguments./', $message);
        });
        $logger = new StdoutLogger(new Config([
            StdoutLoggerInterface::class => [
                'log_level' => [
                    LogLevel::ERROR,
                ],
            ],
        ]), $output);

        $logger->error(new RuntimeException('Invalid Arguments.'));
    }
}
