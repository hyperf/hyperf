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

namespace Hyperf\DbConnection;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\Connection as DatabaseConnection;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionMetadata;
use Hyperf\Database\Schema\Grammars\Grammar as SchemaGrammar;
use LogicException;
use Throwable;

/**
 * One borrow from the pool. Only its owning session may use or return it.
 * @internal
 */
final class ConnectionLease
{
    private int $owner;

    private int $generation;

    private ?ConnectionMetadata $original = null;

    private ?SchemaGrammar $originalSchemaGrammar = null;

    public function __construct(private ?Connection $connection)
    {
        $this->owner = Coroutine::id();
        $this->generation = $connection->getLeaseGeneration();
        $database = $connection->getDatabaseConnection();
        if ($database instanceof DatabaseConnection) {
            $this->original = new ConnectionMetadata(
                $database->getConfig(),
                clone $database->getQueryGrammar(),
                $database->getPostProcessor(),
                $database->getDatabaseName(),
                $database->getTablePrefix()
            );
        }
    }

    public function getConnection(): Connection
    {
        if ($this->owner !== Coroutine::id() || $this->connection === null || $this->connection->getLeaseGeneration() !== $this->generation) {
            throw new LogicException('The database lease is closed or belongs to another coroutine.');
        }
        return $this->connection;
    }

    public function getDatabaseConnection(): ConnectionInterface
    {
        return $this->getConnection()->getDatabaseConnection();
    }

    public function applyMetadata(ConnectionMetadata $metadata): void
    {
        $database = $this->getDatabaseConnection();
        if ($database instanceof DatabaseConnection) {
            $this->originalSchemaGrammar ??= clone $database->getSchemaGrammar();
            $database->setQueryGrammar($metadata->grammar);
            $database->setTablePrefix($metadata->prefix);
            $database->setPostProcessor($metadata->processor);
            $database->setDatabaseName($metadata->database);
            $database->getSchemaGrammar()->setTablePrefix($metadata->prefix);
        }
    }

    public function release(bool $invalidate = false): void
    {
        if ($this->connection === null) {
            return;
        }
        $connection = $this->getConnection();
        $this->connection = null;
        try {
            if ($invalidate) {
                $connection->invalidate();
            } elseif ($this->original !== null) {
                $database = $connection->getDatabaseConnection();
                if ($database instanceof DatabaseConnection) {
                    $database->setQueryGrammar($this->original->grammar);
                    $database->setTablePrefix($this->original->prefix);
                    $database->setPostProcessor($this->original->processor);
                    $database->setDatabaseName($this->original->database);
                    if ($this->originalSchemaGrammar !== null) {
                        $database->setSchemaGrammar($this->originalSchemaGrammar);
                    }
                }
            }
        } catch (Throwable $exception) {
            // Cleanup must not replace a query/callback exception. Discard the
            // driver and report the reset failure before returning its pool slot.
            $connection->invalidate($exception);
        } finally {
            $connection->release();
        }
    }
}
