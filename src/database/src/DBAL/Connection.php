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

namespace Hyperf\Database\DBAL;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Exception\IdentityColumnsNotSupported;
use Doctrine\DBAL\Driver\Exception\NoIdentityValue;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDO\Result;
use Doctrine\DBAL\Driver\PDO\Statement;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use PDO;
use PDOException;
use PDOStatement;

use function assert;

class Connection implements ConnectionInterface
{
    /**
     * Create a new PDO connection instance.
     */
    public function __construct(protected PDO $connection)
    {
    }

    /**
     * Execute an SQL statement.
     */
    public function exec(string $sql): int
    {
        try {
            $result = $this->connection->exec($sql);

            assert($result !== false);

            return $result;
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Prepare a new SQL statement.
     */
    public function prepare(string $sql): Statement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            assert($stmt instanceof PDOStatement);

            return new Statement($stmt);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Execute a new query against the connection.
     */
    public function query(string $sql): ResultInterface
    {
        try {
            $stmt = $this->connection->query($sql);
            assert($stmt instanceof PDOStatement);

            return new Result($stmt);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Get the last insert ID.
     *
     * @return string
     */
    public function lastInsertId(): int|string
    {
        try {
            $value = $this->connection->lastInsertId();
        } catch (PDOException $exception) {
            assert($exception->errorInfo !== null);
            [$sqlState] = $exception->errorInfo;

            // if the PDO driver does not support this capability, PDO::lastInsertId() triggers an IM001 SQLSTATE
            // see https://www.php.net/manual/en/pdo.lastinsertid.php
            if ($sqlState === 'IM001') {
                throw IdentityColumnsNotSupported::new();
            }

            // PDO PGSQL throws a 'lastval is not yet defined in this session' error when no identity value is
            // available, with SQLSTATE 55000 'Object Not In Prerequisite State'
            if ($sqlState === '55000' && $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                throw NoIdentityValue::new($exception);
            }

            throw Exception::new($exception);
        }

        // pdo_mysql & pdo_sqlite return '0', pdo_sqlsrv returns '' or false depending on the PHP version
        if ($value === '0' || $value === '' || $value === false) {
            throw NoIdentityValue::new();
        }

        return $value;
    }

    /**
     * Begin a new database transaction.
     */
    public function beginTransaction(): void
    {
        try {
            $this->connection->beginTransaction();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Commit a database transaction.
     */
    public function commit(): void
    {
        try {
            $this->connection->commit();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Roll back a database transaction.
     */
    public function rollBack(): void
    {
        try {
            $this->connection->rollBack();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Wrap quotes around the given input.
     */
    public function quote(string $value): string
    {
        return $this->connection->quote($value);
    }

    /**
     * Get the server version for the connection.
     */
    public function getServerVersion(): string
    {
        return $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Get the wrapped PDO connection.
     */
    public function getWrappedConnection(): PDO
    {
        return $this->connection;
    }

    public function getNativeConnection()
    {
        return $this->connection;
    }

    /**
     * Create a new statement instance.
     */
    protected function createStatement(PDOStatement $stmt): Statement
    {
        return new Statement($stmt);
    }
}
