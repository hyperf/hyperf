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
use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\PgSQL\Concerns\PostgreSqlSwooleExtManagesTransactions;
use Hyperf\Database\PgSQL\DBAL\PostgresDriver;
use Hyperf\Database\PgSQL\Query\Grammars\PostgresSqlSwooleExtGrammar as QueryGrammar;
use Hyperf\Database\PgSQL\Query\Processors\PostgresProcessor;
use Hyperf\Database\PgSQL\Schema\Grammars\PostgresSqlSwooleExtGrammar as SchemaGrammar;
use Hyperf\Database\PgSQL\Schema\PostgresBuilder;

class PostgreSqlSwooleExtConnection extends Connection
{
    use PostgreSqlSwooleExtManagesTransactions;

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
     * Execute an SQL statement and return the boolean result.
     */
    public function statement(string $query, array $bindings = []): bool
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $this->getPdo();

            $id = $this->prepare($query);

            $this->recordsHaveBeenModified();

            return (bool) $this->pdo->execute($id, $this->prepareBindings($bindings));
        });
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     */
    public function affectingStatement(string $query, array $bindings = []): int
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            $this->getPdo();

            $id = $this->prepare($query);
            $this->recordsHaveBeenModified();

            $result = $this->pdo->execute($id, $this->prepareBindings($bindings));

            $this->recordsHaveBeenModified(
                ($count = $this->pdo->affectedRows($result)) > 0
            );

            return $count;
        });
    }

    /**
     * Run a select statement against the database.
     */
    public function select(string $query, array $bindings = [], bool $useReadPdo = true): array
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            $this->getPdoForSelect($useReadPdo);

            $id = $this->prepare($query);

            $result = $this->pdo->execute($id, $this->prepareBindings($bindings));

            if ($result === false) {
                throw new QueryException($query, [], new \Exception($this->pdo->error));
            }

            return $this->pdo->fetchAll($result) ?: [];
        });
    }

    /**
     * Run a select statement against the database and returns a generator.
     */
    public function cursor(string $query, array $bindings = [], bool $useReadPdo = true): \Generator
    {
        $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            $this->getPdoForSelect($useReadPdo);

            $id = $this->prepare($query);

            $this->pdo->execute($id, $this->prepareBindings($bindings));

            return $this->pdo;
        });

        while ($record = $this->fetch($query, $bindings)) {
            yield $record;
        }
    }

    public function fetch(string $query, array $bindings = [])
    {
        $records = $this->queryAll($query, $bindings);

        return array_shift($records);
    }

    public function queryAll(string $query, array $bindings = []): array
    {
        $statement = $this->prepare($query);

        $result = $this->pdo->execute($statement, $bindings);
        if ($result === false) {
            throw new QueryException($query, [], new \Exception($this->pdo->error));
        }

        return $this->pdo->fetchAll($result) ?: [];
    }

    public function str_replace_once($needle, $replace, $haystack)
    {
        // Looks for the first occurence of $needle in $haystack
        // and replaces it with $replace.
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            // Nothing found
            return $haystack;
        }

        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    /**
     * Get the default query grammar instance.
     * @return \Hyperf\Database\Grammar
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
     *
     * @return \Hyperf\Database\Query\Processors\PostgresProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new PostgresProcessor();
    }

    /**
     * Get the Doctrine DBAL driver.
     */
    protected function getDoctrineDriver(): PostgresDriver
    {
        return new PostgresDriver();
    }

    protected function prepare(string $query): string
    {
        $num = 1;
        while (strpos($query, '?')) {
            $query = $this->str_replace_once('?', '$' . $num++, $query);
        }

        $id = uniqid();
        $res = $this->pdo->prepare($id, $query);
        if ($res === false) {
            throw new QueryException($query, [], new \Exception($this->pdo->error));
        }

        return $id;
    }
}
