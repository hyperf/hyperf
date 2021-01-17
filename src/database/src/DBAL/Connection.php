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

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDO\Result;
use Doctrine\DBAL\Driver\PDO\Statement;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use PDO;
use PDOException;
use PDOStatement;

class Connection implements ServerInfoAwareConnection
{
    /**
     * The underlying PDO connection.
     *
     * @var PDO
     */
    protected $connection;

    /**
     * Create a new PDO connection instance.
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Execute an SQL statement.
     */
    public function exec(string $sql): int
    {
        try {
            $result = $this->connection->exec($sql);

            \assert($result !== false);

            return $result;
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Prepare a new SQL statement.
     */
    public function prepare(string $sql): StatementInterface
    {
        try {
            return $this->createStatement(
                $this->connection->prepare($sql)
            );
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

            \assert($stmt instanceof PDOStatement);

            return new Result($stmt);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Get the last insert ID.
     *
     * @param null|string $name
     * @return string
     */
    public function lastInsertId($name = null)
    {
        try {
            if ($name === null) {
                return $this->connection->lastInsertId();
            }

            return $this->connection->lastInsertId($name);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Begin a new database transaction.
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a database transaction.
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Roll back a database transaction.
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
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
        return $this->connection->quote($input, $type);
    }

    /**
     * Get the server version for the connection.
     *
     * @return string
     */
    public function getServerVersion()
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

    /**
     * Create a new statement instance.
     */
    protected function createStatement(PDOStatement $stmt): Statement
    {
        return new Statement($stmt);
    }
}
