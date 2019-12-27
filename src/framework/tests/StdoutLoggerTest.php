<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
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
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @internal
 * @coversNothing
 */
class StdoutLoggerTest extends TestCase
{
    protected function tearDown()
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
}
