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
use Hyperf\Database\Connection;
use Hyperf\Database\Schema\MySqlBuilder;
use Hyperf\DbConnection\Pool\PoolFactory;
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
     * @var PoolFactory
     */
    protected $factory;

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
        $this->factory = $container->get(PoolFactory::class);

        $parserFactory = new ParserFactory();
        $this->astParser = $parserFactory->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
    }

    protected function configure()
    {
        $this->addArgument('table', InputArgument::OPTIONAL, 'Which table you want create.')
            ->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, 'Which pool you want use.', 'default')
            ->addOption('path', 'path', InputOption::VALUE_OPTIONAL, 'Which pool you want use.', 'app/Models');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $table = $input->getArgument('table');
        $pool = $input->getOption('pool');
        $path = $input->getOption('path');
        $path = BASE_PATH . '/' . $path;
        if ($table) {
            $this->createModel($table, $pool, $path);
        } else {
            $this->createModels($pool, $path);
        }
    }

    /**
     * @return MySqlBuilder
     */
    protected function getSchemaBuilder($poolName)
    {
        $pool = $this->factory->getDbPool($poolName);
        /** @var Connection $connection */
        $connection = $pool->get()->getConnection();
        return $connection->getSchemaBuilder();
    }

    protected function createModels($pool, $path)
    {
        $builder = $this->getSchemaBuilder($pool);
        $tables = [];

        foreach ($builder->getAllTables() as $row) {
            $row = (array) $row;
            $tables[] = reset($row);
        }

        foreach ($tables as $table) {
            $this->createModel($table, $pool, $path);
        }
    }

    protected function createModel($table, $poolName, $path)
    {
        $builder = $this->getSchemaBuilder($poolName);

        $columns = $builder->getColumnListing($table);

        $class = Str::studly($table);
        $path = $path . '/' . $class . '.php';
        if (! file_exists($path)) {
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
        $code = '<?php declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class %s extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = \'%s\';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
}';

        return sprintf($code, Str::studly($table), $table);
    }
}
