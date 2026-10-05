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

    private ?Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->owner = Coroutine::id();
        $this->generation = $connection->getLeaseGeneration();
        if ($this->generation === 0) {
            throw new LogicException('A database lease requires an actively borrowed connection.');
        }
        $database = $connection->getDatabaseConnection();
        if ($database instanceof DatabaseConnection) {
            $this->original = new ConnectionMetadata(
                $database->getConfig(),
                clone $database->getQueryGrammar(),
                $database->getPostProcessor(),
                $database->getDatabaseName(),
                $database->getTablePrefix()
            );
            $schemaGrammar = $database->getInitializedSchemaGrammar();
            $this->originalSchemaGrammar = $schemaGrammar === null ? null : clone $schemaGrammar;
        }
        $connection->setReleaseCallback($this->generation, fn (Connection $connection) => $this->restoreMetadata($connection));
    }

    public function isActive(): bool
    {
        return $this->owner === Coroutine::id() && $this->connection !== null
            && $this->connection->getLeaseGeneration() === $this->generation;
    }

    public function getConnection(): Connection
    {
        if ($this->owner !== Coroutine::id() || $this->connection === null) {
            throw new LogicException('The database lease is closed or belongs to another coroutine.');
        }
        if ($this->connection->getLeaseGeneration() !== $this->generation) {
            throw new LogicException('The database lease has already been returned to the pool.');
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
            $database->setQueryGrammar($metadata->grammar);
            $database->setTablePrefix($metadata->prefix);
            $database->setPostProcessor($metadata->processor);
            $database->setDatabaseName($metadata->database);
            $database->getInitializedSchemaGrammar()?->setTablePrefix($metadata->prefix);
        }
    }

    public function release(bool $invalidate = false): void
    {
        if ($this->connection === null) {
            return;
        }
        if ($this->owner !== Coroutine::id()) {
            throw new LogicException('The database lease belongs to another coroutine.');
        }
        $connection = $this->connection;
        $this->connection = null;
        // An external return may have transferred this wrapper to a new borrower.
        // Its cleanup owns the current generation; never reset or invalidate it here.
        if ($connection->getLeaseGeneration() !== $this->generation) {
            return;
        }
        if ($invalidate) {
            $connection->invalidate();
        }
        $connection->release();
    }

    private function restoreMetadata(Connection $connection): void
    {
        if ($this->original === null) {
            return;
        }
        // Invalidated drivers have no state to restore and reconnect on the next borrow.
        $database = $connection->getDatabaseConnection();
        if ($database instanceof DatabaseConnection) {
            // Restore the grammar first so setTablePrefix cannot mutate session metadata.
            $database->setQueryGrammar($this->original->grammar);
            $database->setTablePrefix($this->original->prefix);
            $database->setPostProcessor($this->original->processor);
            $database->setDatabaseName($this->original->database);
            $database->restoreSchemaGrammar($this->originalSchemaGrammar);
        }
    }
}
