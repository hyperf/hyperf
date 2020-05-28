<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\DbSQLiteDriver;

use Doctrine\DBAL\Driver\PDOSqlite\Driver as DoctrineDriver;
use Hyperf\Database\Connection;
use Hyperf\DbSQLiteDriver\Query\Grammars\SQLiteGrammar as QueryGrammar;
use Hyperf\DbSQLiteDriver\Query\Processors\SQLiteProcessor;
use Hyperf\DbSQLiteDriver\Schema\Grammars\SQLiteGrammar as SchemaGrammar;
use Hyperf\DbSQLiteDriver\Schema\SQLiteBuilder;

class SQLiteConnection extends Connection
{
    /**
     * Create a new database connection instance.
     *
     * @param \Closure|\PDO $pdo
     * @param string $database
     * @param string $tablePrefix
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);

        $enableForeignKeyConstraints = $this->getForeignKeyConstraintsConfigurationValue();

        if ($enableForeignKeyConstraints === null) {
            return;
        }

        $enableForeignKeyConstraints
            ? $this->getSchemaBuilder()->enableForeignKeyConstraints()
            : $this->getSchemaBuilder()->disableForeignKeyConstraints();
    }

    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SQLiteBuilder($this);
    }

    /**
     * Get the default query grammar instance.
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar());
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar());
    }

    /**
     * Get the default post processor instance.
     */
    protected function getDefaultPostProcessor()
    {
        return new SQLiteProcessor();
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOSqlite\Driver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver();
    }

    /**
     * Get the database connection foreign key constraints configuration option.
     *
     * @return null|bool
     */
    protected function getForeignKeyConstraintsConfigurationValue()
    {
        return $this->getConfig('foreign_key_constraints');
    }
}
