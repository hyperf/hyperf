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
namespace Hyperf\Database\Commands\Seeders;

use Hyperf\Database\Seeders\SeederCreator;
use Hyperf\Utils\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenSeederCommand extends BaseCommand
{
    /**
     * The seeder creator instance.
     *
     * @var \Hyperf\Database\Seeders\SeederCreator
     */
    protected $creator;

    /**
     * Create a new seeder generator command instance.
     */
    public function __construct(SeederCreator $creator)
    {
        parent::__construct('gen:seeder');
        $this->setDescription('Create a new seeder class');

        $this->creator = $creator;
    }

    /**
     * Handle the current command.
     */
    public function handle()
    {
        $name = Str::snake(trim($this->input->getArgument('name')));

        $this->writeMigration($name);
    }

    /**
     * Write the seeder file to disk.
     */
    protected function writeMigration(string $name)
    {
        $path = $this->ensureSeederDirectoryAlreadyExist(
            $this->getSeederPath()
        );

        $file = pathinfo($this->creator->create($name, $path), PATHINFO_FILENAME);

        $this->info("<info>[INFO] Created Seeder:</info> {$file}");
    }

    protected function ensureSeederDirectoryAlreadyExist(string $path)
    {
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the seeder'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the seeder file should be created'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided seeder file paths are pre-resolved absolute paths'],
        ];
    }
}
