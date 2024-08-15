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

namespace HyperfTest\Command\Command\Annotation;

use Hyperf\Command\Annotation\AsCommand;
use RuntimeException;

class TestAsCommand
{
    #[AsCommand('command:as-command:run')]
    public function run()
    {
        return 'run';
    }

    #[AsCommand('command:as-command:runWithDefinedOptions {--name=}')]
    public function runWithDefinedOptions(string $name)
    {
        return 'runWithDefinedOptions';
    }

    #[AsCommand('command:as-command:runWithoutOptions')]
    public function runWithoutOptions(string $name, int $age = 9, bool $testBool = false)
    {
        return 'runWithoutOptions';
    }

    #[AsCommand('command:as-command:runStatic')]
    protected static function runStatic()
    {
        throw new RuntimeException('command:as-command:runStatic');
    }
}
