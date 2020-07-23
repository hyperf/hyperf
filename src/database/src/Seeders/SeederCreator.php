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

use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Str;
use InvalidArgumentException;

class SeederCreator
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new seeder creator instance.
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new seeder at the given path.
     *
     * @param string $name
     * @param string $path
     * @return string
     */
    public function create($name, $path)
    {
        $this->ensureSeederDoesntAlreadyExist($name);

        $stub = $this->getStub();

        $this->files->put(
            $path = $this->getPath($name, $path),
            $this->populateStub($name, $stub)
        );

        return $path;
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__ . '/stubs';
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Hyperf\Utils\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get the seeder stub file.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->files->get($this->stubPath() . '/seeder.stub');
    }

    /**
     * Populate the place-holders in the seeder stub.
     *
     * @param string $name
     * @param string $stub
     * @return string
     */
    protected function populateStub($name, $stub)
    {
        return str_replace('DummyClass', $this->getClassName($name), $stub);
    }

    /**
     * Ensure that a seeder with the given name doesn't already exist.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     */
    protected function ensureSeederDoesntAlreadyExist($name)
    {
        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    /**
     * Get the class name of a seeder name.
     *
     * @param string $name
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studly($name);
    }

    /**
     * Get the full path to the seeder.
     *
     * @param string $name
     * @param string $path
     * @return string
     */
    protected function getPath($name, $path)
    {
        return $path . '/' . $name . '.php';
    }
}
