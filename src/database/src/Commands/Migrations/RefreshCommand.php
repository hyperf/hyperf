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
use Symfony\Component\Console\Input\InputOption;

class RefreshCommand extends Command
{
    use ConfirmableTrait;

    public function __construct()
    {
        parent::__construct('migrate:refresh');
        $this->setDescription('Reset and re-run all migrations');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        // Next we'll gather some of the options so that we can have the right options
        // to pass to the commands. This includes options such as which database to
        // use and the path to use for the migration. Then we'll run the command.
        $connection = $this->input->getOption('database') ?? 'default';

        $path = $this->input->getOption('path') ?? '';

        // If the "step" option is specified it means we only want to rollback a small
        // number of migrations before migrating again. For example, the user might
        // only rollback and remigrate the latest four migrations instead of all.
        $step = (int) $this->input->getOption('step') ?: 0;

        if ($step > 0) {
            $this->runRollback($connection, $path, $step);
        } else {
            $this->runReset($connection, $path);
        }

        // The refresh command is essentially just a brief aggregate of a few other of
        // the migration commands and just provides a convenient wrapper to execute
        // them in succession. We'll also see if we need to re-seed the database.
        $this->call('migrate', array_filter([
            '--database' => $connection,
            '--path' => $path,
            '--realpath' => $this->input->getOption('realpath'),
            '--force' => true,
        ]));

        if ($this->needsSeeding()) {
            $this->runSeeder($connection);
        }
    }

    /**
     * Run the rollback command.
     */
    protected function runRollback(string $database, string $path, int $step): void
    {
        $this->call('migrate:rollback', array_filter([
            '--database' => $database,
            '--path' => $path,
            '--realpath' => $this->input->getOption('realpath'),
            '--step' => $step,
            '--force' => true,
        ]));
    }

    /**
     * Run the reset command.
     */
    protected function runReset(string $database, string $path): void
    {
        $this->call('migrate:reset', array_filter([
            '--database' => $database,
            '--path' => $path,
            '--realpath' => $this->input->getOption('realpath'),
            '--force' => true,
        ]));
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
    protected function runSeeder(string $database): void
    {
        $this->call('db:seed', array_filter([
            '--database' => $database,
            '--force' => true,
        ]));
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
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to be executed'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
            ['seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder'],
            ['step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted & re-run'],
        ];
    }
}
