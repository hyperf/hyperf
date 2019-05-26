<?php

namespace Hyperf\Database\Commands\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Hyperf\Database\Migrations\MigrationRepositoryInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the migration repository';

    /**
     * The repository instance.
     *
     * @var MigrationRepositoryInterface
     */
    protected $repository;

    /**
     * The input interface implementation.
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * The output interface implementation.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Create a new migration install command instance.
     *
     * @param  \Hyperf\Database\Migrations\MigrationRepositoryInterface $repository
     * @return void
     */
    public function __construct(MigrationRepositoryInterface $repository)
    {
        parent::__construct();
        $this->specifyParameters();

        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->repository->setSource($input->getOption('database'));

        $this->repository->createRepository();

        $output->writeln('<info>Migration table created successfully.</info>');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
        ];
    }

    /**
     * Specify the arguments and options on the command.
     */
    protected function specifyParameters(): void
    {
        // We will loop through all of the arguments and options for the command and
        // set them all on the base command instance. This specifies what can get
        // passed into these commands as "parameters" to control the execution.
        if (method_exists($this, 'getArguments')) {
            foreach ($this->getArguments() ?? [] as $arguments) {
                call_user_func_array([$this, 'addArgument'], $arguments);
            }
        }

        if (method_exists($this, 'getOptions')) {
            foreach ($this->getOptions() ?? [] as $options) {
                call_user_func_array([$this, 'addOption'], $options);
            }
        }
    }
}
