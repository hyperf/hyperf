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
namespace Hyperf\Database\PgSQL;

use Hyperf\Database\Connection;
use Hyperf\Database\PgSQL\DBAL\PostgresPdoDriver;
use Hyperf\Database\PgSQL\Query\Grammars\PostgresGrammar as QueryGrammar;
use Hyperf\Database\PgSQL\Query\Processors\PostgresProcessor;
use Hyperf\Database\PgSQL\Schema\Grammars\PostgresGrammar as SchemaGrammar;
use Hyperf\Database\PgSQL\Schema\PostgresBuilder;
use PDOStatement;

class PostgreSqlConnection extends Connection
{
    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder(): PostgresBuilder
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new PostgresBuilder($this);
    }

    /**
     * Bind values to their parameters in the given statement.
     */
    public function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value
            );
        }
    }

    /**
     * Get the default query grammar instance.
     * @return \Hyperf\Database\Query\Grammars\PostgresGrammar
     */
    protected function getDefaultQueryGrammar(): QueryGrammar
    {
        return $this->withTablePrefix(new QueryGrammar());
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): SchemaGrammar
    {
        return $this->withTablePrefix(new SchemaGrammar());
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Hyperf\Database\Query\Processors\PostgresProcessor
     */
    protected function getDefaultPostProcessor(): PostgresProcessor
    {
        return new PostgresProcessor();
    }

    /**
     * Get the Doctrine DBAL driver.
     */
    protected function getDoctrineDriver(): PostgresPdoDriver
    {
        return new PostgresPdoDriver();
    }
}
