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

namespace HyperfTest\Command;

use ErrorException;
use Hyperf\Command\Concerns\InteractsWithIO;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class InteractsWithIOTest extends TestCase
{
    #[DataProvider('verbosityProvider')]
    public function testLineVerbosity(null|int|string $level, int $default, int $expected): void
    {
        $command = new class {
            use InteractsWithIO {
                setVerbosity as public;
            }
        };
        $command->setVerbosity($default);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with('text', $expected);
        $command->setOutput($output);

        set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }, E_DEPRECATED);

        try {
            $command->line('text', null, $level);
        } finally {
            restore_error_handler();
        }
    }

    public static function verbosityProvider(): array
    {
        return [
            'default normal' => [null, OutputInterface::VERBOSITY_NORMAL, OutputInterface::VERBOSITY_NORMAL],
            'default verbose' => [null, OutputInterface::VERBOSITY_VERBOSE, OutputInterface::VERBOSITY_VERBOSE],
            'verbose' => ['v', OutputInterface::VERBOSITY_NORMAL, OutputInterface::VERBOSITY_VERBOSE],
            'very verbose' => ['vv', OutputInterface::VERBOSITY_NORMAL, OutputInterface::VERBOSITY_VERY_VERBOSE],
            'debug' => ['vvv', OutputInterface::VERBOSITY_NORMAL, OutputInterface::VERBOSITY_DEBUG],
            'quiet' => ['quiet', OutputInterface::VERBOSITY_NORMAL, OutputInterface::VERBOSITY_QUIET],
            'normal' => ['normal', OutputInterface::VERBOSITY_DEBUG, OutputInterface::VERBOSITY_NORMAL],
            'integer quiet' => [OutputInterface::VERBOSITY_QUIET, OutputInterface::VERBOSITY_NORMAL, OutputInterface::VERBOSITY_QUIET],
            'integer debug' => [OutputInterface::VERBOSITY_DEBUG, OutputInterface::VERBOSITY_NORMAL, OutputInterface::VERBOSITY_DEBUG],
            'unknown' => ['unknown', OutputInterface::VERBOSITY_VERBOSE, OutputInterface::VERBOSITY_VERBOSE],
            'empty' => ['', OutputInterface::VERBOSITY_VERBOSE, OutputInterface::VERBOSITY_VERBOSE],
        ];
    }
}
