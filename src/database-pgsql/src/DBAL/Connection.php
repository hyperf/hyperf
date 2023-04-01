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
namespace Hyperf\Database\PgSQL\DBAL;

use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use Swoole\Coroutine\PostgreSQL;
use Swoole\Coroutine\PostgreSQLStatement;

use function assert;

class Connection implements \Doctrine\DBAL\Driver\Connection
{
    /**
     * Create a new PDO connection instance.
     */
    public function __construct(private PostgreSQL $connection)
    {
    }

    /**
     * Execute an SQL statement.
     */
    public function exec(string $sql): int
    {
        $stmt = $this->connection->query($sql);

        assert($stmt instanceof PostgreSQLStatement);

        return $stmt->affectedRows();
    }

    /**
     * Prepare a new SQL statement.
     */
    public function prepare(string $sql): StatementInterface
    {
        $stmt = $this->connection->prepare($sql);

        assert($stmt instanceof PostgreSQLStatement);

        return new Statement($stmt);
    }

    /**
     * Execute a new query against the connection.
     */
    public function query(string $sql): ResultInterface
    {
        $stmt = $this->connection->query($sql);

        assert($stmt instanceof PostgreSQLStatement);

        return new Result($stmt);
    }

    /**
     * Get the last insert ID.
     *
     * @param null|string $name
     * @return string
     */
    public function lastInsertId($name = null)
    {
        if ($name !== null) {
            return $this->query(sprintf('SELECT CURRVAL(%s)', $this->quote($name)))->fetchOne();
        }

        return $this->query('SELECT LASTVAL()')->fetchOne();
    }

    /**
     * Begin a new database transaction.
     */
    public function beginTransaction(): bool
    {
        $this->exec('BEGIN');

        return true;
    }

    /**
     * Commit a database transaction.
     */
    public function commit(): bool
    {
        $this->exec('COMMIT');

        return true;
    }

    /**
     * Roll back a database transaction.
     */
    public function rollBack(): bool
    {
        $this->exec('ROLLBACK');

        return true;
    }

    /**
     * Wrap quotes around the given input.
     *
     * @param string $input
     * @param string $type
     * @return string
     */
    public function quote($input, $type = ParameterType::STRING)
    {
        return $this->connection->escapeLiteral($input);
    }

    /**
     * Get the server version for the connection.
     */
    public function getServerVersion(): string
    {
        $result = $this->query('SHOW server_version');

        $serverVersion = $result->fetchOne();
        if ($version = strstr($serverVersion, ' ', true)) {
            return $version;
        }

        return $serverVersion;
    }

    public function getNativeConnection(): PostgreSQL
    {
        return $this->connection;
    }
}
