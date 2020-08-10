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
use Hyperf\Utils\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class ResourceCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:transformer');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('create a new transformer');
        $this->addOption('collection', 'c', InputOption::VALUE_NONE, 'Create a transformer collection');
        $this->addOption('grpc', null, InputOption::VALUE_NONE, 'Create a transformer collection');
    }

    protected function getStub(): string
    {
        return $this->isGrpc()
            ? __DIR__ . '/stubs/transformer-grpc.stub'
            : ($this->isCollection()
                ? __DIR__ . '/stubs/transformer-collection.stub'
                : __DIR__ . '/stubs/transformer.stub');
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Transformer';
    }

    protected function isCollection()
    {
        return $this->input->getOption('collection') ||
            Str::endsWith($this->input->getArgument('name'), 'Collection');
    }

    protected function isGrpc()
    {
        return $this->input->getOption('grpc');
    }
}
