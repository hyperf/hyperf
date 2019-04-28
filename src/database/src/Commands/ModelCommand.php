<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Commands;

use Hyperf\Database\Commands\Ast\ModelUpdateVistor;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Schema\MySqlBuilder;
use Hyperf\Utils\Str;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->addArgument('table', InputArgument::OPTIONAL, 'Which table you want to associated with the Model.');
        $this->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, 'Which connection pool you want the Model use.', 'default');
        $this->addOption('path', 'pt', InputOption::VALUE_OPTIONAL, 'The path that you want the Model file to be generated.', 'app/Models');
        $this->addOption('force-casts', 'fc', InputOption::VALUE_OPTIONAL, 'Whether force generate the casts for model.', false);
        $this->addOption('prefix', 'pf', InputOption::VALUE_OPTIONAL, 'What prefix that you want the Model set.', '');
        $this->addOption('inheritance', 'i', InputOption::VALUE_OPTIONAL, 'The inheritance that you want the Model extends.', 'Model');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $table = $input->getArgument('table');

        $option = new ModelOption();
        $option->setPool($input->getOption('pool'))
            ->setPath($input->getOption('path'))
            ->setPrefix($input->getOption('prefix'))
            ->setForceCasts($input->getOption('force-casts') !== false)
            ->setInheritance($input->getOption('inheritance'));

        // $path = BASE_PATH . '/' . $path;
        if ($table) {
            $this->createModel($table, $option);
        } else {
            $this->createModels($option);
        }
    }

    protected function getSchemaBuilder(string $poolName): MySqlBuilder
    {
        $connection = $this->resolver->connection($poolName);
        return $connection->getSchemaBuilder();
    }

    protected function createModels(ModelOption $option)
    {
        $builder = $this->getSchemaBuilder($option->getPool());
        $tables = [];

        foreach ($builder->getAllTables() as $row) {
            $row = (array) $row;
            $tables[] = reset($row);
        }

        foreach ($tables as $table) {
            $this->createModel($table, $option);
        }
    }

    protected function createModel(string $table, ModelOption $option)
    {
        $builder = $this->getSchemaBuilder($option->getPool());

        $columns = $builder->getColumnTypeListing($table);

        $class = $option->getPath() . '/' . Str::studly($table);
        $path = BASE_PATH . '/' . $class . '.php';

        if (! file_exists($path)) {
            $dir = dirname($path);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $class = str_replace('/', '\\', Str::ucfirst($class));
            file_put_contents($path, $this->buildClass($class, $option));
        }

        $stms = $this->astParser->parse(file_get_contents($path));
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ModelUpdateVistor($columns, $option->isForceCasts()));
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

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name, ModelOption $option)
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');

        return $this->replaceNamespace($stub, $name)
            ->replaceInheritance($stub, $option->getInheritance())
            ->replaceConnection($stub, $option->getPool())
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['%NAMESPACE%'],
            [$this->getNamespace($name)],
            $stub
        );

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    protected function replaceInheritance(&$stub, $inheritance)
    {
        $stub = str_replace(
            ['%INHERITANCE%'],
            [$inheritance],
            $stub
        );

        return $this;
    }

    protected function replaceConnection(&$stub, $connection)
    {
        $stub = str_replace(
            ['%CONNECTION%'],
            [$connection],
            $stub
        );

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = str_replace('%CLASS%', $class, $stub);

        return str_replace('%TABLE%', Str::snake($class), $stub);
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        return BASE_PATH . '/' . str_replace('\\', '/', $name) . '.php';
    }
}
