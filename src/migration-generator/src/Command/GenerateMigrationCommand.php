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
namespace Hyperf\MigrationGenerator\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\MigrationGenerator\MigrationGenerator;
use Hyperf\Support\Filesystem\Filesystem;
use PhpParser\PrettyPrinterAbstract;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

class GenerateMigrationCommand extends HyperfCommand
{
    protected ?ConnectionResolverInterface $resolver = null;

    protected ?ConfigInterface $config = null;

    protected ?PrettyPrinterAbstract $printer = null;

    protected ?Filesystem $files = null;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('gen:migration-from-database');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generate migrations from an existing table structure');
        $this->addArgument('table', InputArgument::OPTIONAL, 'Which table you want generated.');
        $this->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, 'The connection pool you want the migration to be generated.', 'default');
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path that you want the migration to be generated.', 'migrations');
    }

    public function handle()
    {
        $table = $this->input->getArgument('table');
        $pool = $this->input->getOption('pool');
        $path = $this->input->getOption('path');

        $this->resolver = $this->container->get(ConnectionResolverInterface::class);
        $this->config = $this->container->get(ConfigInterface::class);

        try {
            $generator = new MigrationGenerator(
                $this->container->get(ConnectionResolverInterface::class),
                $this->container->get(ConfigInterface::class),
                $this->output
            );

            $generator->generate($pool, $path, $table);
        } catch (Throwable $e) {
            $this->error("<error>[ERROR] Created Migration:</error> {$e->getMessage()}");
        }
    }
}
