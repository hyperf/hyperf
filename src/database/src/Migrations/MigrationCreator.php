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

use Closure;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Str;
use InvalidArgumentException;

class MigrationCreator
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The registered post create hooks.
     *
     * @var array
     */
    protected $postCreate = [];

    /**
     * Create a new migration creator instance.
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new migration at the given path.
     *
     * @throws \Exception
     */
    public function create(string $name, string $path, string $table = null, bool $create = false): string
    {
        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $this->files->put(
            $path = $this->getPath($name, $path),
            $this->populateStub($name, $stub, $table)
        );

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table);

        return $path;
    }

    /**
     * Register a post migration create hook.
     */
    public function afterCreate(Closure $callback)
    {
        $this->postCreate[] = $callback;
    }

    /**
     * Get the path to the stubs.
     */
    public function stubPath(): string
    {
        return __DIR__ . '/stubs';
    }

    /**
     * Get the filesystem instance.
     */
    public function getFilesystem(): Filesystem
    {
        return $this->files;
    }

    /**
     * Ensure that a migration with the given name doesn't already exist.
     *
     * @throws \InvalidArgumentException
     */
    protected function ensureMigrationDoesntAlreadyExist(string $name, ?string $migrationPath = null)
    {
        if (! empty($migrationPath)) {
            $migrationFiles = $this->files->glob($migrationPath . '/*.php');

            foreach ($migrationFiles as $migrationFile) {
                $this->files->requireOnce($migrationFile);
            }
        }

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    /**
     * Get the migration stub file.
     */
    protected function getStub(?string $table, bool $create): string
    {
        if (is_null($table)) {
            return $this->files->get($this->stubPath() . '/blank.stub');
        }

        // We also have stubs for creating new tables and modifying existing tables
        // to save the developer some typing when they are creating a new tables
        // or modifying existing tables. We'll grab the appropriate stub here.
        $stub = $create ? 'create.stub' : 'update.stub';

        return $this->files->get($this->stubPath() . "/{$stub}");
    }

    /**
     * Populate the place-holders in the migration stub.
     */
    protected function populateStub(string $name, string $stub, ?string $table): string
    {
        $stub = str_replace('DummyClass', $this->getClassName($name), $stub);

        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if (! is_null($table)) {
            $stub = str_replace('DummyTable', $table, $stub);
        }

        return $stub;
    }

    /**
     * Get the class name of a migration name.
     */
    protected function getClassName(string $name): string
    {
        return Str::studly($name);
    }

    /**
     * Get the full path to the migration.
     */
    protected function getPath(string $name, string $path): string
    {
        return $path . '/' . $this->getDatePrefix() . '_' . $name . '.php';
    }

    /**
     * Fire the registered post create hooks.
     */
    protected function firePostCreateHooks(?string $table)
    {
        foreach ($this->postCreate as $callback) {
            call_user_func($callback, $table);
        }
    }

    /**
     * Get the date prefix for the migration.
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }
}
