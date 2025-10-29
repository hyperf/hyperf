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
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ResourceCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:resource');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('create a new resource');
        $this->addOption('collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection');
        $this->addOption('grpc', null, InputOption::VALUE_NONE, 'Create a grpc resource');
    }

    protected function getStub(): string
    {
        return $this->isGrpc()
            ? __DIR__ . '/stubs/resource-grpc.stub'
            : ($this->isCollection()
                ? __DIR__ . '/stubs/resource-collection.stub'
                : __DIR__ . '/stubs/resource.stub');
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\Resource';
    }

    protected function isCollection(): bool
    {
        return $this->input->getOption('collection')
            || Str::endsWith($this->input->getArgument('name'), 'Collection');
    }

    protected function isGrpc(): bool
    {
        return $this->input->getOption('grpc');
    }
}
