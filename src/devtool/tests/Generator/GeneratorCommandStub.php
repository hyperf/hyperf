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

namespace HyperfTest\Devtool\Generator;

use Hyperf\Devtool\Generator\GeneratorCommand;
use Symfony\Component\Console\Input\InputInterface;

class GeneratorCommandStub extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:test-stub');
        $this->setDescription('Test stub command');
    }

    public function exposedGetPath(string $name): string
    {
        return $this->getPath($name);
    }

    public function setTestInput(InputInterface $input): void
    {
        $this->input = $input;
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/class.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return 'App\Test';
    }
}
