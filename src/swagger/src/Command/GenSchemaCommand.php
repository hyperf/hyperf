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
namespace Hyperf\Swagger\Command;

use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class GenSchemaCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('gen:swagger-schema');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generate swagger schemas.');
        $this->addOption('name', 'N', InputOption::VALUE_OPTIONAL, 'The schema name.');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Whether to force generate the schema.');
    }

    public function handle()
    {
        $name = $this->input->getOption('name');
        $force = $this->input->getOption('force');

        $path = BASE_PATH . '/app/Schema/' . $name . '.php';
        if (file_exists($path) && ! $force) {
            $this->output->error('The file is exists.');
            return;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/schema.stub');

        $code = str_replace('%NAME', $name, $stub);

        file_put_contents($path, $code);
    }
}
