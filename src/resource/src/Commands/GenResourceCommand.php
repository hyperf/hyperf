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
namespace Hyperf\Resource\Commands;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Hyperf\Utils\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class GenResourceCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:resource');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('create a new resource');
        $this->addOption('collection', 'c', InputOption::VALUE_OPTIONAL, 'Create a resource collection');
    }

    protected function getStub(): string
    {
        return $this->collection()
            ? __DIR__ . '/stubs/resource-collection.stub'
            : __DIR__ . '/stubs/resource.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Resource';
    }

    protected function collection()
    {
        return $this->input->getOption('collection') ||
            Str::endsWith($this->input->getArgument('name'), 'Collection');
    }
}
