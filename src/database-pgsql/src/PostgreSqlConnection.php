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

use DateTimeInterface;
use Exception;
use Hyperf\Database\Connection;
use Hyperf\Database\PgSQL\DBAL\PostgresPdoDriver;
use Hyperf\Database\PgSQL\Query\Grammars\PostgresGrammar as QueryGrammar;
use Hyperf\Database\PgSQL\Query\Processors\PostgresProcessor;
use Hyperf\Database\PgSQL\Schema\Grammars\PostgresGrammar as SchemaGrammar;
use Hyperf\Database\PgSQL\Schema\PostgresBuilder;
use Hyperf\Database\Query\Grammars\PostgresGrammar;
use PDO;
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
     * Prepare the query bindings for execution.
     *
     * Converts booleans to 'true'/'false' when emulated prepares are enabled,
     * as PostgreSQL rejects integer literals for boolean columns.
     *
     * @see https://github.com/laravel/framework/issues/37261
     */
    public function prepareBindings(array $bindings): array
    {
        if (! $this->isUsingEmulatedPrepares()) {
            return parent::prepareBindings($bindings);
        }

        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }

    /**
     * Escape a boolean value for safe SQL embedding.
     */
    protected function escapeBool(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    /**
     * Determine if the connection is using emulated prepares.
     */
    protected function isUsingEmulatedPrepares(): bool
    {
        return ($this->config['options'][PDO::ATTR_EMULATE_PREPARES] ?? false) === true;
    }

    /**
     * Determine if the given database exception was caused by a unique constraint violation.
     *
     * @return bool
     */
    protected function isUniqueConstraintError(Exception $exception)
    {
        return $exception->getCode() === '23505';
    }

    /**
     * Get the default query grammar instance.
     * @return PostgresGrammar
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
