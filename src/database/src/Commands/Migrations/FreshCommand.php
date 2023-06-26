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
namespace Hyperf\Database\Commands\Migrations;

use Hyperf\Command\Command;
use Hyperf\Command\Concerns\Confirmable as ConfirmableTrait;
use Hyperf\Database\ConnectionResolverInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class FreshCommand extends Command
{
    use ConfirmableTrait;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('migrate:fresh');
        $this->setDescription('Drop all tables and re-run all migrations');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $connection = $this->input->getOption('database') ?? 'default';

        if ($this->input->getOption('drop-views')) {
            $this->dropAllViews($connection);

            $this->info('Dropped all views successfully.');
        }

        $this->dropAllTables($connection);

        $this->info('Dropped all tables successfully.');

        $this->call('migrate', array_filter([
            '--database' => $connection,
            '--path' => $this->input->getOption('path'),
            '--realpath' => $this->input->getOption('realpath'),
            '--force' => true,
            '--step' => $this->input->getOption('step'),
        ]));

        if ($this->needsSeeding()) {
            $this->runSeeder($connection);
        }
    }

    /**
     * Drop all the database tables.
     */
    protected function dropAllTables(string $connection)
    {
        $this->container->get(ConnectionResolverInterface::class)
            ->connection($connection)
            ->getSchemaBuilder()
            ->dropAllTables();
    }

    /**
     * Drop all the database views.
     */
    protected function dropAllViews(string $connection)
    {
        $this->container->get(ConnectionResolverInterface::class)
            ->connection($connection)
            ->getSchemaBuilder()
            ->dropAllViews();
    }

    /**
     * Determine if the developer has requested database seeding.
     */
    protected function needsSeeding(): bool
    {
        return $this->input->getOption('seed') || $this->input->getOption('seeder');
    }

    /**
     * Run the database seeder command.
     */
    protected function runSeeder(string $database)
    {
        $this->call('db:seed', array_filter([
            '--database' => $database,
            '--force' => true,
        ]));
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
            ['drop-views', null, InputOption::VALUE_NONE, 'Drop all tables and views'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to be executed'],
            [
                'realpath',
                null,
                InputOption::VALUE_NONE,
                'Indicate any provided migration file paths are pre-resolved absolute paths',
            ],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
            ['seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder'],
            [
                'step',
                null,
                InputOption::VALUE_NONE,
                'Force the migrations to be run so they can be rolled back individually',
            ],
        ];
    }
}
