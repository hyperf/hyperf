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

namespace Hyperf\Framework\Logger;

use Hyperf\Contract\StdoutLoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger as SymfonyConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends SymfonyConsoleLogger implements StdoutLoggerInterface
{
    public function __construct(?OutputInterface $output = null, array $verbosityLevelMap = [], array $formatLevelMap = [])
    {
        $verbosity = function (): int {
            $argv = $_SERVER['argv'] ?? [];
            $argv = is_string($argv) ? explode(' ', $argv) : $argv;

            foreach ($argv as $arg) {
                if ($arg === '-v') {
                    return OutputInterface::VERBOSITY_VERBOSE;
                }

                if ($arg === '-vv') {
                    return OutputInterface::VERBOSITY_VERY_VERBOSE;
                }

                if ($arg === '-vvv') {
                    return OutputInterface::VERBOSITY_DEBUG;
                }

                if ($arg === '-q' || $arg === '-quiet') {
                    return OutputInterface::VERBOSITY_QUIET;
                }
            }

            return OutputInterface::VERBOSITY_NORMAL;
        };
        $output = $output ?? new ConsoleOutput($verbosity());

        parent::__construct($output, $verbosityLevelMap, $formatLevelMap);
    }
}
