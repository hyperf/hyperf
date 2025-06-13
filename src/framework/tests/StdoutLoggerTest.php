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
use HyperfTest\Framework\Stub\TestObject;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class StdoutLoggerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testLog()
    {
        $logger = $this->getLogger('<info>[INFO]</> Hello Hyperf.');
        $logger->info('Hello {name}.', ['name' => 'Hyperf']);
    }

    public function testFixedErrorContextCount()
    {
        $logger = $this->getLogger('<info>[INFO]</> [test tag] Hello Hyperf.');
        $logger->info('Hello {name}.', [
            'component' => 'test tag',
            'name' => 'Hyperf',
        ]);
    }

    public function testLogComplexityContext()
    {
        $logger = $this->getLogger('<info>[INFO]</> [test tag] Hello Hyperf <OBJECT> HyperfTest\Framework\Stub\TestObject.');
        $logger->info('Hello {name} {object}.', [
            'name' => 'Hyperf',
            // tags
            'component' => 'test tag',
            // object can not be cast to string
            'object' => new TestObject(),
        ]);
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

    protected function getLogger($expected): StdoutLogger
    {
        $output = Mockery::mock(ConsoleOutput::class);
        $output->shouldReceive('writeln')->with(Mockery::any())->once()->andReturnUsing(function ($message) use ($expected) {
            $this->assertSame($expected, $message);
        });
        return new StdoutLogger(new Config([
            StdoutLoggerInterface::class => [
                'log_level' => [
                    LogLevel::INFO,
                ],
            ],
        ]), $output);
    }
}
