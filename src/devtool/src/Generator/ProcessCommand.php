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

namespace Hyperf\Devtool\Generator;

use Hyperf\Framework\Annotation\Command;

/**
 * @Command
 */
class ProcessCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:process');
        $this->setDescription('Create a new process class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/process.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Processes';
    }
}
