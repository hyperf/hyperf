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

namespace Hyperf\MigrationGenerator;

use Hyperf\Collection\Collection;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Schema\Builder;
use Hyperf\Database\Schema\Column;
use Hyperf\Database\Schema\MySqlBuilder;
use Hyperf\Support\Filesystem\Filesystem;
use InvalidArgumentException;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Symfony\Component\Console\Output\OutputInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

class MigrationGenerator
{
    protected Parser $astParser;

    protected PrettyPrinterAbstract $printer;

    protected Filesystem $files;

    public function __construct(
        protected ConnectionResolverInterface $resolver,
        protected ConfigInterface $config,
        protected ?OutputInterface $output = null,
    ) {
        $this->astParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
        $this->files = make(Filesystem::class);
    }

    public function generate(string $pool, string $path, ?string $table)
    {
        $option = tap(new ModelOption(), static function (ModelOption $option) use ($pool, $path) {
            $option->setPool($pool);
            $option->setPath($path);
        });

        if ($table) {
            $this->createMigration($table, $option);
        } else {
            $this->createMigrations($option);
        }
    }

    public function getTableData(ModelOption $option, ?string $table = null, ?string $database = null): TableData
    {
        $columns = $this->getColumnArray($option, $table, $database);
        $indexes = $this->getIndexes($option, $table, $database);
        $comment = $this->getComment($option, $table);

        return new TableData($columns, $indexes, $comment);
    }

    public function getComment(ModelOption $option, ?string $table = null): string
    {
        // SHOW TABLE STATUS LIKE 'acl_role';
        $connection = $this->resolver->connection($option->getPool());
        $result = $connection->select(sprintf(
            'SHOW TABLE STATUS LIKE "%s";',
            $table
        ));
        $result = array_change_key_case((array) $result[0], CASE_LOWER);
        return $result['comment'];
    }

    public function getIndexes(ModelOption $option, ?string $table = null, ?string $database = null): array
    {
        $connection = $this->resolver->connection($option->getPool());
        $query = $connection->select(sprintf(
            'SHOW INDEX FROM `%s`.`%s`;',
            $database ?? $this->config->get('databases.' . $option->getPool() . '.database'),
            $table
        ));

        $result = [];
        foreach ($query as $item) {
            $result[] = array_change_key_case((array) $item, CASE_LOWER);
        }
        return $result;
    }

    public function getColumnArray(ModelOption $option, ?string $table = null, ?string $database = null): array
    {
        $connection = $this->resolver->connection($option->getPool());
        $query = $connection->select('select * from information_schema.columns where `table_schema` = ? and `table_name` = ? order by ORDINAL_POSITION', [
            $database ?? $this->config->get('databases.' . $option->getPool() . '.database'),
            $table,
        ]);
        $result = [];
        foreach ($query as $item) {
            $result[] = array_change_key_case((array) $item, CASE_LOWER);
        }
        return $result;
    }

    public function getColumns(ModelOption $option, ?string $table = null): Collection
    {
        $pool = $option->getPool();
        $columns = Context::getOrSet('database.columns.' . $pool, function () use ($pool) {
            $builder = $this->getSchemaBuilder($pool);
            return $builder->getColumns();
        });

        if ($table) {
            return collect($columns)->filter(static function (Column $column) use ($table) {
                return $column->getTable() === $table;
            })->sort(static function (Column $a, Column $b) {
                return $a->getPosition() - $b->getPosition();
            });
        }

        return collect($columns);
    }

    public function createMigration(string $table, ModelOption $option)
    {
        if (! defined('BASE_PATH')) {
            throw new InvalidArgumentException('Please set constant `BASE_PATH`.');
        }

        $stub = __DIR__ . '/../stubs/create_from_database.stub.php';
        if (! file_exists($stub)) {
            $stub = BASE_PATH . '/vendor/migration-generator-incubator/stubs/create_from_database.stub.php';
            if (! file_exists($stub)) {
                throw new InvalidArgumentException('create_from_database.stub does not exists.');
            }
        }

        $columns = $this->getColumns($option, $table);
        $tableData = $this->getTableData($option, $table);

        $code = file_get_contents($stub);
        $stmts = $this->astParser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CreateMigrationVisitor($table, $option, $columns, $tableData));
        $stmts = $traverser->traverse($stmts);
        $code = $this->printer->prettyPrintFile($stmts);

        $path = BASE_PATH . '/' . $option->getPath();
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $this->files->put(
            $path = $this->getPath('create_' . $table . '_table', $path),
            $code
        );

        $file = pathinfo($path, PATHINFO_FILENAME);

        $this->line("<info>[INFO] Created Migration:</info> {$file}", 'info');
    }

    public function createMigrations(ModelOption $option)
    {
        /** @var MySqlBuilder $builder */
        $builder = $this->getSchemaBuilder($option->getPool());
        $tables = [];

        foreach ($builder->getAllTables() as $row) {
            $row = (array) $row;
            $table = reset($row);
            if (! $this->isIgnoreTable($table, $option)) {
                $tables[] = $table;
            }
        }

        foreach ($tables as $table) {
            $this->createMigration($table, $option);
        }
    }

    public function line($string, $style = null)
    {
        $styled = $style ? "<{$style}>{$string}</{$style}>" : $string;
        $this->output?->writeln($styled);
    }

    protected function isIgnoreTable(string $table, ModelOption $option): bool
    {
        if (in_array($table, $option->getIgnoreTables())) {
            return true;
        }

        return $table === $this->config->get('databases.migrations', 'migrations');
    }

    protected function getSchemaBuilder(string $poolName): Builder
    {
        /** @var Connection $connection */
        $connection = $this->resolver->connection($poolName);
        return $connection->getSchemaBuilder();
    }

    /**
     * Get the full path to the migration.
     */
    protected function getPath(string $name, string $path): string
    {
        return $path . '/' . $this->getDatePrefix() . '_' . $name . '.php';
    }

    /**
     * Get the date prefix for the migration.
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }
}
