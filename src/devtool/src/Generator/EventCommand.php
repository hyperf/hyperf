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
namespace Hyperf\Devtool\Generator;

use Hyperf\Command\Annotation\Command;

/**
 * @Command
 */
class EventCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:event');
        $this->setDescription('Create a new event class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/event.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Event';
    }
}
