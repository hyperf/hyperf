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

namespace Hyperf\Database\Migrations;

use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolverInterface as Resolver;
use Hyperf\Database\Schema\Grammars\Grammar;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function Hyperf\Collection\collect;

class Migrator
{
    /**
     * The name of the default connection.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * The paths to all of the migration files.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * The output interface implementation.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Create a new migrator instance.
     */
    public function __construct(
        protected MigrationRepositoryInterface $repository,
        protected Resolver $resolver,
        protected Filesystem $files
    ) {
    }

    /**
     * Run the pending migrations at a given path.
     *
     * @throws Throwable
     */
    public function run(array|string $paths = [], array $options = []): array
    {
        // Once we grab all of the migration files for the path, we will compare them
        // against the migrations that have already been run for this package then
        // run each of the outstanding migrations against a database connection.
        $files = $this->getMigrationFiles($paths);

        $this->requireFiles($migrations = $this->pendingMigrations(
            $files,
            $this->repository->getRan()
        ));

        // Once we have all these migrations that are outstanding we are ready to run
        // we will go ahead and run them "up". This will execute each migration as
        // an operation against a database. Then we'll return this list of them.
        $this->runPending($migrations, $options);

        return $migrations;
    }

    /**
     * Run an array of migrations.
     *
     * @throws Throwable
     */
    public function runPending(array $migrations, array $options = []): void
    {
        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($migrations) === 0) {
            $this->note('<info>Nothing to migrate.</info>');

            return;
        }

        // Next, we will get the next batch number for the migrations so we can insert
        // correct batch number in the database migrations repository when we store
        // each migration's execution. We will also extract a few of the options.
        $batch = $this->repository->getNextBatchNumber();

        $pretend = $options['pretend'] ?? false;

        $step = $options['step'] ?? false;

        // Once we have the array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
        foreach ($migrations as $file) {
            $this->runUp($file, $batch, $pretend);

            if ($step) {
                ++$batch;
            }
        }
    }

    /**
     * Rollback the last migration operation.
     *
     * @throws Throwable
     */
    public function rollback(array|string $paths = [], array $options = []): array
    {
        // We want to pull in the last batch of migrations that ran on the previous
        // migration operation. We'll then reverse those migrations and run each
        // of them "down" to reverse the last migration "operation" which ran.
        $migrations = $this->getMigrationsForRollback($options);

        if (count($migrations) === 0) {
            $this->note('<info>Nothing to rollback.</info>');

            return [];
        }

        return $this->rollbackMigrations($migrations, $paths, $options);
    }

    /**
     * Rolls all of the currently applied migrations back.
     */
    public function reset(array|string $paths = [], bool $pretend = false): array
    {
        // Next, we will reverse the migration list so we can run them back in the
        // correct order for resetting this database. This will allow us to get
        // the database back into its "empty" state ready for the migrations.
        $migrations = array_reverse($this->repository->getRan());

        if (count($migrations) === 0) {
            $this->note('<info>Nothing to rollback.</info>');

            return [];
        }

        return $this->resetMigrations($migrations, $paths, $pretend);
    }

    /**
     * Resolve a migration instance from a file.
     */
    public function resolve(string $file): object
    {
        $class = $this->getMigrationClass($file);

        return new $class();
    }

    /**
     * Get all of the migration files in a given path.
     */
    public function getMigrationFiles(array|string $paths): array
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return Str::endsWith($path, '.php') ? [$path] : $this->files->glob($path . '/*_*.php');
        })->filter()->sortBy(function ($file) {
            return $this->getMigrationName($file);
        })->values()->keyBy(function ($file) {
            return $this->getMigrationName($file);
        })->all();
    }

    /**
     * Require in all the migration files in a given path.
     */
    public function requireFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }
    }

    /**
     * Get the name of the migration.
     */
    public function getMigrationName(string $path): string
    {
        return str_replace('.php', '', basename($path));
    }

    /**
     * Register a custom migration path.
     */
    public function path(string $path): void
    {
        $this->paths = array_unique(array_merge($this->paths, [$path]));
    }

    /**
     * Get all of the custom migration paths.
     */
    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * Get the default connection name.
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * Set the default connection name.
     */
    public function setConnection(string $name): void
    {
        $this->resolver->setDefaultConnection($name);

        $this->repository->setSource($name);

        $this->connection = $name;
    }

    /**
     * Resolve the database connection instance.
     *
     * @return Connection the return object maybe is a non-extends proxy class, so DONOT define the return type
     */
    public function resolveConnection(string $connection)
    {
        return $this->resolver->connection($connection ?: $this->connection);
    }

    /**
     * Get the migration repository instance.
     */
    public function getRepository(): MigrationRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Determine if the migration repository exists.
     */
    public function repositoryExists(): bool
    {
        return $this->repository->repositoryExists();
    }

    /**
     * Get the file system instance.
     */
    public function getFilesystem(): Filesystem
    {
        return $this->files;
    }

    /**
     * Set the output implementation that should be used by the console.
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Resolve a migration instance from migration path.
     */
    protected function resolvePath(string $path): object
    {
        $class = $this->getMigrationClass($this->getMigrationName($path));
        if (class_exists($class)) {
            return new $class();
        }

        return $this->files->getRequire($path);
    }

    /**
     * Generate migration class name based on migration name.
     */
    protected function getMigrationClass(string $migrationName): string
    {
        return Str::studly(implode('_', array_slice(explode('_', $migrationName), 4)));
    }

    /**
     * Get the migration files that have not yet run.
     */
    protected function pendingMigrations(array $files, array $ran): array
    {
        return Collection::make($files)
            ->reject(function ($file) use ($ran) {
                return in_array($this->getMigrationName($file), $ran);
            })->values()->all();
    }

    /**
     * Run "up" a migration instance.
     *
     * @throws Throwable
     */
    protected function runUp(string $file, int $batch, bool $pretend): void
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolvePath($file);
        $name = $this->getMigrationName($file);

        if ($pretend) {
            $this->pretendToRun($migration, 'up');
            return;
        }

        $this->note("<comment>Migrating:</comment> {$name}");

        $this->runMigration($migration, 'up');

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
        $this->repository->log($name, $batch);

        $this->note("<info>Migrated:</info>  {$name}");
    }

    /**
     * Get the migrations for a rollback operation.
     */
    protected function getMigrationsForRollback(array $options): array
    {
        if (($steps = $options['step'] ?? 0) > 0) {
            return $this->repository->getMigrations($steps);
        }

        return $this->repository->getLast();
    }

    /**
     * Rollback the given migrations.
     *
     * @param array|string $paths
     * @throws Throwable
     */
    protected function rollbackMigrations(array $migrations, $paths, array $options): array
    {
        $rolledBack = [];

        $this->requireFiles($files = $this->getMigrationFiles($paths));

        // Next we will run through all of the migrations and call the "down" method
        // which will reverse each migration in order. This getLast method on the
        // repository already returns these migration's names in reverse order.
        foreach ($migrations as $migration) {
            $migration = (object) $migration;

            if (! $file = Arr::get($files, $migration->migration)) {
                $this->note("<fg=red>Migration not found:</> {$migration->migration}");

                continue;
            }

            $rolledBack[] = $file;

            $this->runDown(
                (string) $file,
                $migration,
                $options['pretend'] ?? false
            );
        }

        return $rolledBack;
    }

    /**
     * Reset the given migrations.
     */
    protected function resetMigrations(array $migrations, array $paths, bool $pretend = false): array
    {
        // Since the getRan method that retrieves the migration name just gives us the
        // migration name, we will format the names into objects with the name as a
        // property on the objects so that we can pass it to the rollback method.
        $migrations = collect($migrations)->map(function ($m) {
            return (object) ['migration' => $m];
        })->all();

        return $this->rollbackMigrations(
            $migrations,
            $paths,
            compact('pretend')
        );
    }

    /**
     * Run "down" a migration instance.
     *
     * @throws Throwable
     */
    protected function runDown(string $file, object $migration, bool $pretend): void
    {
        // First we will get the file name of the migration so we can resolve out an
        // instance of the migration. Once we get an instance we can either run a
        // pretend execution of the migration or we can run the real migration.
        $instance = $this->resolvePath($file);
        $name = $this->getMigrationName($file);

        $this->note("<comment>Rolling back:</comment> {$name}");

        if ($pretend) {
            $this->pretendToRun($instance, 'down');
            return;
        }

        $this->runMigration($instance, 'down');

        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
        $this->repository->delete($migration);

        $this->note("<info>Rolled back:</info>  {$name}");
    }

    /**
     * Run a migration inside a transaction if the database supports it.
     *
     * @throws Throwable
     */
    protected function runMigration(object $migration, string $method): void
    {
        $connection = $this->resolveConnection(
            $migration->getConnection()
        );

        $callback = function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $defaultConnection = $this->resolver->getDefaultConnection();
                $this->resolver->setDefaultConnection($migration->getConnection() ?: $this->connection);

                $migration->{$method}();

                $this->resolver->setDefaultConnection($defaultConnection);
            }
        };

        $this->getSchemaGrammar($connection)->supportsSchemaTransactions()
        && $migration->withinTransaction
            ? $connection->transaction($callback)
            : $callback();
    }

    /**
     * Pretend to run the migrations.
     */
    protected function pretendToRun(object $migration, string $method): void
    {
        foreach ($this->getQueries($migration, $method) as $query) {
            $name = get_class($migration);

            $reflectionClass = new ReflectionClass($migration);
            if ($reflectionClass->isAnonymous()) {
                $name = $this->getMigrationName($reflectionClass->getFileName());
            }

            $this->note("<info>{$name}:</info> {$query['query']}");
        }
    }

    /**
     * Get all of the queries that would be run for a migration.
     */
    protected function getQueries(object $migration, string $method): array
    {
        // Now that we have the connections we can resolve it and pretend to run the
        // queries against the database returning the array of raw SQL statements
        // that would get fired against the database system for this migration.
        $db = $this->resolveConnection(
            $migration->getConnection()
        );

        return $db->pretend(function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $migration->{$method}();
            }
        });
    }

    /**
     * Get the schema grammar out of a migration connection.
     *
     * @param Connection $connection the return object maybe is a non-extends proxy class, so DONOT define the return type
     */
    protected function getSchemaGrammar($connection): Grammar
    {
        return $connection->getSchemaGrammar();
    }

    /**
     * Write a note to the conosle's output.
     */
    protected function note(string $message): void
    {
        $this->output?->writeln($message);
    }
}
