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

namespace Hyperf\Database\Seeders;

use Hyperf\Collection\Collection;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolverInterface as Resolver;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Grammars\Grammar;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

class Seed
{
    /**
     * The name of the default connection.
     */
    protected ?string $connection = null;

    /**
     * The paths to all the seeder files.
     */
    protected array $paths = [];

    /**
     * The output interface implementation.
     */
    protected ?OutputInterface $output = null;

    /**
     * Create a new seed instance.
     */
    public function __construct(protected Resolver $resolver, protected Filesystem $files)
    {
    }

    public function path(string $path): void
    {
        $this->paths = array_unique(array_merge($this->paths, [$path]));
    }

    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * Run the pending seeders at a given path.
     *
     * @param array|string $paths
     * @return array
     */
    public function run($paths = [], array $options = [])
    {
        $files = $this->getSeederFiles($paths);

        $this->requireFiles($files);

        $this->runSeeders($files, $options);

        return $files;
    }

    /**
     * Set the default connection name.
     */
    public function setConnection(string $name): void
    {
        $this->resolver->setDefaultConnection($name);
        $this->connection = $name;
    }

    /**
     * Run an array of seeders.
     */
    public function runSeeders(array $seeders, array $options = [])
    {
        if (count($seeders) === 0) {
            return;
        }

        foreach ($seeders as $file) {
            $this->runSeeder($file);
        }
    }

    /**
     * Run a seeder.
     *
     * @param string $file
     */
    public function runSeeder($file)
    {
        Model::unguarded(function () use ($file) {
            $seeder = $this->resolve(
                $name = $this->getSeederName($file)
            );

            $this->note("<comment>Seed:</comment> {$name}");

            $connection = $this->resolveConnection(
                $seeder->getConnection()
            );

            $callback = function () use ($seeder) {
                if (method_exists($seeder, 'run')) {
                    $seeder->run();
                }
            };

            if ($this->getSchemaGrammar($connection)->supportsSchemaTransactions() && $seeder->withinTransaction) {
                $connection->transaction($callback);
            } else {
                $callback();
            }

            $this->note("<info>Seeded:</info> {$name}");
        });
    }

    /**
     * Resolve a seeder instance from a file.
     */
    public function resolve(string $file): object
    {
        $class = Str::studly($file);

        return new $class();
    }

    /**
     * Resolve the database connection instance.
     *
     * @return Connection
     */
    public function resolveConnection(string $connection)
    {
        return $this->resolver->connection($connection ?: $this->connection);
    }

    /**
     * Get all the seeder files in a given path.
     *
     * @param array|string $paths
     * @return array
     */
    public function getSeederFiles($paths)
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return Str::endsWith($path, '.php') ? [$path] : $this->files->glob($path . '/*.php');
        })->filter()->sortBy(function ($file) {
            return $this->getSeederName($file);
        })->values()->keyBy(function ($file) {
            return $this->getSeederName($file);
        })->all();
    }

    /**
     * Get the name of the seeder.
     *
     * @param string $path
     * @return string
     */
    public function getSeederName($path)
    {
        return str_replace('.php', '', basename($path));
    }

    /**
     * Require in all the seeder files in a given path.
     */
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }
    }

    /**
     * Set the output implementation that should be used by the console.
     */
    public function setOutput(OutputInterface $output): static
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Get the schema grammar out of a migration connection.
     *
     * @param Connection $connection
     */
    protected function getSchemaGrammar($connection): Grammar
    {
        return $connection->getSchemaGrammar();
    }

    /**
     * Write a note to the console's output.
     *
     * @param string $message
     */
    protected function note($message): void
    {
        $this->output?->writeln($message);
    }
}
