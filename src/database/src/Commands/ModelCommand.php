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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Commands\Ast\ModelUpdateVistor;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Schema\MySqlBuilder;
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
     * @var ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * @var ConfigInterface
     */
    protected $config;

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

    /**
     * @var InputInterface
     */
    protected $input;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('db:model');
        $this->container = $container;
    }

    protected function configure()
    {
        $this->addArgument('table', InputArgument::OPTIONAL, 'Which table you want to associated with the Model.');

        $this->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, 'Which connection pool you want the Model use.', 'default');
        $this->addOption('path', 'pt', InputOption::VALUE_OPTIONAL, 'The path that you want the Model file to be generated.', false);
        $this->addOption('force-casts', 'F', InputOption::VALUE_OPTIONAL, 'Whether force generate the casts for model.', false);
        $this->addOption('prefix', 'pf', InputOption::VALUE_OPTIONAL, 'What prefix that you want the Model set.', false);
        $this->addOption('inheritance', 'i', InputOption::VALUE_OPTIONAL, 'The inheritance that you want the Model extends.', false);
    }

    protected function init(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->resolver = $this->container->get(ConnectionResolverInterface::class);
        $this->config = $this->container->get(ConfigInterface::class);
        $this->astParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);

        $table = $input->getArgument('table');
        $pool = $input->getOption('pool');

        $option = new ModelOption();
        $option->setPool($pool)
            ->setPath($this->getOption('path', 'commands.db:model.path', $pool, 'app/Model'))
            ->setPrefix($this->getOption('prefix', 'prefix', $pool, ''))
            ->setForceCasts($this->getOption('force-casts', 'commands.db:model.force_casts', $pool, false) !== false)
            ->setInheritance($this->getOption('inheritance', 'commands.db:model.inheritance', $pool, 'Model'));

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
        $table = Str::replaceFirst($option->getPrefix(), '', $table);
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
        $visitor = make(ModelUpdateVistor::class, ['columns' => $columns, 'forceCasts' => $option->isForceCasts()]);
        $traverser->addVisitor($visitor);
        $stms = $traverser->traverse($stms);
        $code = $this->printer->prettyPrintFile($stms);

        file_put_contents($path, $code);
        $this->output->writeln(sprintf('<info>Model %s was created.</info>', $class));
    }

    protected function getOption(string $name, string $key, string $pool = 'default', $default = null)
    {
        $result = $this->input->getOption($name);
        if ($result === false) {
            $result = $this->config->get("databases.{$pool}.{$key}", $default);
        }

        return $result;
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass(string $name, ModelOption $option): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');

        return $this->replaceNamespace($stub, $name)
            ->replaceInheritance($stub, $option->getInheritance())
            ->replaceConnection($stub, $option->getPool())
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the namespace for the given stub.
     */
    protected function replaceNamespace(string &$stub, string $name): self
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
     */
    protected function getNamespace(string $name): string
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    protected function replaceInheritance(string &$stub, string $inheritance): self
    {
        $stub = str_replace(
            ['%INHERITANCE%'],
            [$inheritance],
            $stub
        );

        return $this;
    }

    protected function replaceConnection(string &$stub, string $connection): self
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
     */
    protected function replaceClass(string $stub, string $name): string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = str_replace('%CLASS%', $class, $stub);

        return str_replace('%TABLE%', Str::snake($class), $stub);
    }

    /**
     * Get the destination class path.
     */
    protected function getPath(string $name): string
    {
        return BASE_PATH . '/' . str_replace('\\', '/', $name) . '.php';
    }
}
