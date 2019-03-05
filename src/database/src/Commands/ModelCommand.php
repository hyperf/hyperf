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

use Hyperf\Utils\Str;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Hyperf\Database\Schema\MySqlBuilder;
use Symfony\Component\Console\Command\Command;
use Hyperf\Database\ConnectionResolverInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Hyperf\Database\Commands\Ast\ModelUpdateVistor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModelCommand extends Command
{
    /**
     * @var ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * @var \PhpParser\Parser
     */
    protected $astParser;

    /**
     * @var \PhpParser\PrettyPrinterAbstract
     */
    protected $printer;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(ConnectionResolverInterface $resolver)
    {
        parent::__construct('db:model');
        $this->resolver = $resolver;

        $parserFactory = new ParserFactory();
        $this->astParser = $parserFactory->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
    }

    protected function configure()
    {
        $this->addArgument('table', InputArgument::OPTIONAL, 'Which table you want to associated with the Model.')
            ->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, 'Which connection pool you want the Model use.', 'default')
            ->addOption('path', 'path', InputOption::VALUE_OPTIONAL, 'The path that you want the Model file to be generated.', 'app/Models')
            ->addOption('force-casts', 'fc', InputOption::VALUE_OPTIONAL, 'Whether force generate the casts for model.', false)
            ->addOption('prefix', 'prefix', InputOption::VALUE_OPTIONAL, 'What prefix that you want the Model set.', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $table = $input->getArgument('table');
        $pool = $input->getOption('pool');
        $path = $input->getOption('path');
        $prefix = $input->getOption('prefix');
        $forceCasts = $input->getOption('force-casts') !== false;

        $path = BASE_PATH . '/' . $path;
        if ($table) {
            $table = Str::replaceFirst($prefix, '', $table);
            $this->createModel($table, $pool, $path, $forceCasts);
        } else {
            $this->createModels($pool, $path, $prefix, $forceCasts);
        }
    }

    protected function getSchemaBuilder(string $poolName): MySqlBuilder
    {
        $connection = $this->resolver->connection($poolName);
        return $connection->getSchemaBuilder();
    }

    protected function createModels(string $pool, string $path, string $prefix, bool $forceCasts)
    {
        $builder = $this->getSchemaBuilder($pool);
        $tables = [];

        foreach ($builder->getAllTables() as $row) {
            $row = (array) $row;
            $tables[] = reset($row);
        }

        foreach ($tables as $table) {
            $table = Str::replaceFirst($prefix, '', $table);
            $this->createModel($table, $pool, $path, $forceCasts);
        }
    }

    protected function createModel(string $table, string $poolName, string $dir, bool $forceCasts)
    {
        $builder = $this->getSchemaBuilder($poolName);

        $columns = $builder->getColumnTypeListing($table);

        $class = Str::studly($table);
        $path = $dir . '/' . $class . '.php';
        if (! file_exists($path)) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $code = $this->getTemplate($table);
            file_put_contents($path, $code);
        }

        $stms = $this->astParser->parse(file_get_contents($path));
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ModelUpdateVistor($columns, $forceCasts));
        $stms = $traverser->traverse($stms);
        $code = $this->printer->prettyPrintFile($stms);

        file_put_contents($path, $code);
        $this->output->writeln(sprintf('<info>Model %s was created.</info>', $class));
    }

    protected function getTemplate(string $table)
    {
        $code = file_get_contents(__DIR__ . '/stubs/Model.stub');
        return sprintf($code, Str::studly($table), $table);
    }
}
