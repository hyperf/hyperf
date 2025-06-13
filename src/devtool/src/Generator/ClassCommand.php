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

namespace Hyperf\Devtool\Generator;

use Hyperf\Command\Annotation\Command;

#[Command]
class ClassCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:class');
    }

    public function configure()
    {
        $this->setDescription('Create a new class');

        parent::configure();
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/class.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App';
    }
}
