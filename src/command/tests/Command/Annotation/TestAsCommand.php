<?php

namespace HyperfTest\Command\Command\Annotation;

use Hyperf\Command\Annotation\AsCommand;

class TestAsCommand
{
    #[AsCommand('command:testAsCommand:run')]
    public function run()
    {
        return 'run';
    }

    #[AsCommand('command:testAsCommand:runWithDefinedOptions {--name=}')]
    public function runWithDefinedOptions(string $name)
    {
        return 'runWithDefinedOptions';
    }

    #[AsCommand('command:testAsCommand:runWithoutOptions')]
    public function runWithoutOptions(string $name, int $age = 9, bool $testBool = false)
    {
        return 'runWithoutOptions';
    }
}
