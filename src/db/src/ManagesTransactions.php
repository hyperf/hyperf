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

namespace Hyperf\DB;

use Throwable;

trait ManagesTransactions
{
    /**
     * Start a new database transaction.
     * @throws Throwable
     */
    public function beginTransaction(): void
    {
        $this->createTransaction();

        ++$this->transactions;
    }

    /**
     * Commit the active database transaction.
     */
    public function commit(): void
    {
        if ($this->transactions == 1) {
            $this->connection->commit();
        }

        $this->transactions = max(0, $this->transactions - 1);
    }

    /**
     * Rollback the active database transaction.
     *
     * @param null|int $toLevel
     *
     * @throws Throwable
     */
    public function rollBack($toLevel = null): void
    {
        // We allow developers to rollback to a certain transaction level. We will verify
        // that this given transaction level is valid before attempting to rollback to
        // that level. If it's not we will just return out and not attempt anything.
        $toLevel = is_null($toLevel)
            ? $this->transactions - 1
            : $toLevel;

        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }

        // Next, we will actually perform this rollback within this database and fire the
        // rollback event. We will also set the current transaction level to the given
        // level that was passed into this method so it will be right from here out.
        try {
            $this->performRollBack($toLevel);
        } catch (Throwable $e) {
            $this->handleRollBackException($e);
        }

        $this->transactions = $toLevel;
    }

    /**
     * Get the number of active transactions.
     */
    public function transactionLevel(): int
    {
        return $this->transactions;
    }

    /**
     * Create a transaction within the database.
     */
    protected function createTransaction()
    {
        if ($this->transactions == 0) {
            try {
                $this->connection->beginTransaction();
            } catch (Throwable $e) {
                $this->handleBeginTransactionException($e);
            }
        } elseif ($this->transactions >= 1) {
            $this->createSavepoint();
        }
    }

    /**
     * Create a save point within the database.
     */
    protected function createSavepoint()
    {
        $this->exec(
            $this->compileSavepoint('trans' . ($this->transactions + 1))
        );
    }

    /**
     * Handle an exception from a transaction beginning.
     *
     * @throws Throwable
     */
    protected function handleBeginTransactionException(Throwable $e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();

            $this->connection->beginTransaction();
        } else {
            throw $e;
        }
    }

    /**
     * Perform a rollback within the database.
     *
     * @param int $toLevel
     */
    protected function performRollBack($toLevel)
    {
        if ($toLevel == 0) {
            $this->connection->rollBack();
        } else {
            $this->exec(
                $this->compileSavepointRollBack('trans' . ($toLevel + 1))
            );
        }
    }

    /**
     * Handle an exception from a rollback.
     *
     * @throws Throwable
     */
    protected function handleRollBackException(Throwable $e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->transactions = 0;
        }

        throw $e;
    }

    /**
     * Compile the SQL statement to define a savepoint.
     */
    protected function compileSavepoint(string $name): string
    {
        return 'SAVEPOINT ' . $name;
    }

    /**
     * Compile the SQL statement to execute a savepoint rollback.
     */
    protected function compileSavepointRollBack(string $name): string
    {
        return 'ROLLBACK TO SAVEPOINT ' . $name;
    }
}
