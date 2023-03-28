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

use Hyperf\Collection\Collection;
use Hyperf\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

class StatusCommand extends BaseCommand
{
    /**
     * Create a new migration rollback command instance.
     */
    public function __construct(protected Migrator $migrator)
    {
        parent::__construct('migrate:status');
        $this->setDescription('Show the status of each migration');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->migrator->setConnection($this->input->getOption('database') ?? 'default');

        if (! $this->migrator->repositoryExists()) {
            return $this->error('Migration table not found.');
        }

        $ran = $this->migrator->getRepository()->getRan();

        $batches = $this->migrator->getRepository()->getMigrationBatches();

        if (count($migrations = $this->getStatusFor($ran, $batches)) > 0) {
            $this->table(['Ran?', 'Migration', 'Batch'], $migrations);
        } else {
            $this->error('No migrations found');
        }
    }

    /**
     * Get the status for the given ran migrations.
     *
     * @return \Hyperf\Collection\Collection
     */
    protected function getStatusFor(array $ran, array $batches)
    {
        return Collection::make($this->getAllMigrationFiles())
            ->map(function ($migration) use ($ran, $batches) {
                $migrationName = $this->migrator->getMigrationName($migration);

                return in_array($migrationName, $ran)
                                ? ['<info>Yes</info>', $migrationName, $batches[$migrationName]]
                                : ['<fg=red>No</fg=red>', $migrationName];
            });
    }

    /**
     * Get an array of all of the migration files.
     *
     * @return array
     */
    protected function getAllMigrationFiles()
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
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
            ['path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to use'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
        ];
    }
}
