<?php
declare(strict_types=1);

namespace Hyperf\Database\Commands\Factories;


use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class GenFactoryCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:factory');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('create a new model factory');
        $this->addOption('model', 'm', InputOption::VALUE_OPTIONAL, 'The name of the model');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/../stubs/Factory.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return '';
    }

    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        return str_replace('/', '\\', $name);
    }

    protected function getPath($name)
    {
        $name = str_replace(
            ['\\', '/'],
            '',
            $this->input->getArgument('name')
        );

        return BASE_PATH . "/factories/{$name}.php";
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $model = $this->input->getOption('model')
            ? $this->qualifyClass($this->input->getOption('model'))
            : 'Model';

        return str_replace(
            '%MODEL%',
            $model,
            parent::buildClass($name)
        );
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }
}