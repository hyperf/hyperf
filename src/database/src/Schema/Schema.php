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

namespace Hyperf\Database\Schema;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;

/**
 * @method static bool hasTable(string $table)
 * @method static bool createDatabase(string $name)
 * @method static bool hasView(string $name)
 * @method static array getViews()
 * @method static bool dropDatabaseIfExists(string $name)
 * @method static array getColumnListing(string $table)
 * @method static array getColumnTypeListing(string $table)
 * @method static void dropAllTables()
 * @method static void dropAllViews()
 * @method static array getAllTables()
 * @method static array getTables()
 * @method static array getAllViews()
 * @method static array getIndexes(string $table)
 * @method static array getIndexListing(string $table)
 * @method static bool hasIndex(string $table, array|string $index, ?string $type = null)
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool hasColumns(string $table, array $columns)
 * @method static void whenTableHasColumn(string $table, string $column, \Closure $callback)
 * @method static void whenTableDoesntHaveColumn(string $table, string $column, \Closure $callback)
 * @method static string getColumnType(string $table, string $column)
 * @method static void table(string $table, \Closure $callback)
 * @method static void create(string $table, \Closure $callback))
 * @method static void drop(string $table)
 * @method static void dropIfExists(string $table)
 * @method static void rename(string $from, string $to)
 * @method static bool enableForeignKeyConstraints()
 * @method static bool disableForeignKeyConstraints()
 * @method static \Hyperf\Database\Connection getConnection()
 * @method static Builder setConnection(\Hyperf\Database\Connection $connection)
 * @method static void blueprintResolver(\Closure $resolver)
 * @method static array getForeignKeys(string $table)
 */
class Schema
{
    public static function __callStatic($name, $arguments)
    {
        $container = ApplicationContext::getContainer();
        $resolver = $container->get(ConnectionResolverInterface::class);
        $connection = $resolver->connection();
        return $connection->getSchemaBuilder()->{$name}(...$arguments);
    }

    public function __call($name, $arguments)
    {
        return self::__callStatic($name, $arguments);
    }

    /**
     * Create a connection by ConnectionResolver.
     */
    public function connection(string $name = 'default'): ConnectionInterface
    {
        $container = ApplicationContext::getContainer();
        $resolver = $container->get(ConnectionResolverInterface::class);
        return $resolver->connection($name);
    }
}
