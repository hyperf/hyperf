<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Commands;

use Hyperf\Database\Commands\Ast\ModelUpdateVistor;
use Hyperf\Database\Schema\MySqlBuilder;
use Hyperf\DbConnection\ConnectionResolver;
use Hyperf\Utils\Str;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ModelCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConnectionResolver
     */
    protected $resolver;

    /**
     * @var \PhpParser\Parser
     */
    protected $astParser;

    /**
     * @var Standard
     */
    protected $printer;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('db:model');
        $this->container = $container;
        $this->resolver = $container->get(ConnectionResolver::class);

        $parserFactory = new ParserFactory();
        $this->astParser = $parserFactory->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
    }

    protected function configure()
    {
        $this->addArgument('table', InputArgument::OPTIONAL, 'Which table you want create.')
            ->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, 'Which pool you want use.', 'default')
            ->addOption('path', 'path', InputOption::VALUE_OPTIONAL, 'Which path you want Models create.', 'app/Models')
            ->addOption('prefix', 'prefix', InputOption::VALUE_OPTIONAL, 'Which prefix you want Model set.', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $table = $input->getArgument('table');
        $pool = $input->getOption('pool');
        $path = $input->getOption('path');
        $prefix = $input->getOption('prefix');

        $path = BASE_PATH . '/' . $path;
        if ($table) {
            $table = Str::replaceFirst($prefix, '', $table);
            $this->createModel($table, $pool, $path);
        } else {
            $this->createModels($pool, $path, $prefix);
        }
    }

    /**
     * @return MySqlBuilder
     */
    protected function getSchemaBuilder($poolName)
    {
        $connection = $this->resolver->connection($poolName);
        return $connection->getSchemaBuilder();
    }

    protected function createModels($pool, $path, $prefix)
    {
        $builder = $this->getSchemaBuilder($pool);
        $tables = [];

        foreach ($builder->getAllTables() as $row) {
            $row = (array) $row;
            $tables[] = reset($row);
        }

        foreach ($tables as $table) {
            $table = Str::replaceFirst($prefix, '', $table);
            $this->createModel($table, $pool, $path);
        }
    }

    protected function createModel($table, $poolName, $dir)
    {
        $builder = $this->getSchemaBuilder($poolName);

        $columns = $builder->getColumnListing($table);

        $class = Str::studly($table);
        $path = $dir . '/' . $class . '.php';
        if (! file_exists($path)) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $code = $this->getOriginCode($table);
            file_put_contents($path, $code);
        }

        $stms = $this->astParser->parse(file_get_contents($path));
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ModelUpdateVistor($columns));
        $stms = $traverser->traverse($stms);
        $code = $this->printer->prettyPrintFile($stms);

        file_put_contents($path, $code);
        $this->output->writeln(sprintf('<info>Model %s is Created!</info>', $class));
    }

    protected function getOriginCode($table)
    {
        $code = file_get_contents(__DIR__ . '/stubs/Model.stub');
        return sprintf($code, Str::studly($table), $table);
    }
}
