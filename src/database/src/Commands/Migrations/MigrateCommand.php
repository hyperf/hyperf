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

use Hyperf\Command\Concerns\Confirmable as ConfirmableTrait;
use Hyperf\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

class MigrateCommand extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * Create a new migration command instance.
     */
    public function __construct(protected Migrator $migrator)
    {
        parent::__construct('migrate');
        $this->setDescription('Run the database migrations');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 0;
        }

        try {
            $this->runMigrations();
        } catch (Throwable $e) {
            if ($this->input->getOption('graceful')) {
                $this->output->warning($e->getMessage());

                return 0;
            }

            throw $e;
        }

        return 0;
    }

    /**
     * Run the pending migrations.
     */
    protected function runMigrations()
    {
        $this->prepareDatabase();

        // Next, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        $this->migrator->setOutput($this->output)
            ->run($this->getMigrationPaths(), [
                'pretend' => $this->input->getOption('pretend'),
                'step' => $this->input->getOption('step'),
            ]);

        // Finally, if the "seed" option has been given, we will re-run the database
        // seed task to re-populate the database, which is convenient when adding
        // a migration and a seed at the same time, as it is only this command.
        if ($this->input->getOption('seed') && ! $this->input->getOption('pretend')) {
            $this->call('db:seed', ['--force' => true]);
        }
    }

    protected function getOptions(): array
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to be executed'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
            ['step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually'],
            ['graceful', null, InputOption::VALUE_NONE, 'Return a successful exit code even if an error occurs'],
        ];
    }

    /**
     * Prepare the migration database for running.
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->input->getOption('database') ?? 'default');

        if (! $this->migrator->repositoryExists()) {
            $this->call('migrate:install', array_filter([
                '--database' => $this->input->getOption('database'),
            ]));
        }
    }
}
