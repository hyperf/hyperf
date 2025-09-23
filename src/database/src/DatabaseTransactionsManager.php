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

namespace Hyperf\Database;

use Hyperf\Collection\Collection;

class DatabaseTransactionsManager
{
    /**
     * All of the committed transactions.
     *
     * @var Collection<int, DatabaseTransactionRecord>
     */
    protected Collection $committedTransactions;

    /**
     * All of the pending transactions.
     *
     * @var Collection<int, DatabaseTransactionRecord>
     */
    protected Collection $pendingTransactions;

    /**
     * The current transaction.
     *
     * @var array
     */
    protected array $currentTransaction = [];

    /**
     * Create a new database transactions manager instance.
     */
    public function __construct()
    {
        $this->committedTransactions = new Collection();
        $this->pendingTransactions = new Collection();
    }

    /**
     * Start a new database transaction.
     *
     * @param string $connection
     * @param int $level
     */
    public function begin(string $connection, int $level): void
    {
        $this->pendingTransactions->push(
            $newTransaction = new DatabaseTransactionRecord(
                $connection,
                $level,
                $this->currentTransaction[$connection] ?? null
            )
        );

        $this->currentTransaction[$connection] = $newTransaction;
    }

    /**
     * Commit the root database transaction and execute callbacks.
     *
     * @param string $connection
     * @param int $levelBeingCommitted
     * @param int $newTransactionLevel
     * @return array
     */
    public function commit(string $connection, int $levelBeingCommitted, int $newTransactionLevel): array
    {
        $this->stageTransactions($connection, $levelBeingCommitted);

        if (isset($this->currentTransaction[$connection])) {
            $this->currentTransaction[$connection] = $this->currentTransaction[$connection]->parent;
        }

        if (! $this->afterCommitCallbacksShouldBeExecuted($newTransactionLevel)
            && $newTransactionLevel !== 0) {
            return [];
        }

        // This method is only called when the root database transaction is committed so there
        // shouldn't be any pending transactions, but going to clear them here anyways just
        // in case. This method could be refactored to receive a level in the future too.
        $this->pendingTransactions = $this->pendingTransactions->reject(
            fn ($transaction) => $transaction->connection === $connection
                && $transaction->level >= $levelBeingCommitted
        )->values();

        [$forThisConnection, $forOtherConnections] = $this->committedTransactions->partition(
            fn ($transaction) => $transaction->connection == $connection
        );

        $this->committedTransactions = $forOtherConnections->values();

        $forThisConnection->map->executeCallbacks();

        return $forThisConnection;
    }

    /**
     * Move relevant pending transactions to a committed state.
     *
     * @param string $connection
     * @param int $levelBeingCommitted
     */
    public function stageTransactions(string $connection, int $levelBeingCommitted): void
    {
        $this->committedTransactions = $this->committedTransactions->merge(
            $this->pendingTransactions->filter(
                fn ($transaction) => $transaction->connection === $connection && $transaction->level >= $levelBeingCommitted
            )
        );

        $this->pendingTransactions = $this->pendingTransactions->reject(
            fn ($transaction) => $transaction->connection === $connection && $transaction->level >= $levelBeingCommitted
        );
    }

    /**
     * Rollback the active database transaction.
     *
     * @param string $connection
     * @param int $newTransactionLevel
     */
    public function rollback(string $connection, int $newTransactionLevel): void
    {
        if ($newTransactionLevel === 0) {
            $this->removeAllTransactionsForConnection($connection);
        } else {
            $this->pendingTransactions = $this->pendingTransactions->reject(
                fn ($transaction) => $transaction->connection == $connection
                    && $transaction->level > $newTransactionLevel
            )->values();

            if ($this->currentTransaction) {
                do {
                    $this->removeCommittedTransactionsThatAreChildrenOf($this->currentTransaction[$connection]);

                    $this->currentTransaction[$connection]->executeCallbacksForRollback();

                    $this->currentTransaction[$connection] = $this->currentTransaction[$connection]->parent;
                } while (
                    isset($this->currentTransaction[$connection])
                    && $this->currentTransaction[$connection]->level > $newTransactionLevel
                );
            }
        }
    }

    /**
     * Register a transaction callback.
     *
     * @param callable $callback
     */
    public function addCallback(callable $callback): void
    {
        if ($current = $this->callbackApplicableTransactions()->last()) {
            $current->addCallback($callback);
            return;
        }

        $callback();
    }

    /**
     * Register a callback for transaction rollback.
     *
     * @param callable $callback
     */
    public function addCallbackForRollback(callable $callback): void
    {
        if ($current = $this->callbackApplicableTransactions()->last()) {
            $current->addCallbackForRollback($callback);
        }
    }

    /**
     * Get the transactions that are applicable to callbacks.
     *
     * @return Collection<int, DatabaseTransactionRecord>
     */
    public function callbackApplicableTransactions(): Collection
    {
        return $this->pendingTransactions;
    }

    /**
     * Determine if after commit callbacks should be executed for the given transaction level.
     *
     * @param int $level
     * @return bool
     */
    public function afterCommitCallbacksShouldBeExecuted(int $level): bool
    {
        return $level === 0;
    }

    /**
     * Get all of the pending transactions.
     *
     * @return Collection
     */
    public function getPendingTransactions(): Collection
    {
        return $this->pendingTransactions;
    }

    /**
     * Get all of the committed transactions.
     *
     * @return Collection
     */
    public function getCommittedTransactions(): Collection
    {
        return $this->committedTransactions;
    }

    /**
     * Remove all pending, completed, and current transactions for the given connection name.
     *
     * @param string $connection
     */
    protected function removeAllTransactionsForConnection(string $connection): void
    {
        if ($this->currentTransaction) {
            for ($currentTransaction = $this->currentTransaction[$connection]; isset($currentTransaction); $currentTransaction = $currentTransaction->parent) {
                $currentTransaction->executeCallbacksForRollback();
            }
        }

        $this->currentTransaction[$connection] = null;

        $this->pendingTransactions = $this->pendingTransactions->reject(
            fn ($transaction) => $transaction->connection == $connection
        )->values();

        $this->committedTransactions = $this->committedTransactions->reject(
            fn ($transaction) => $transaction->connection == $connection
        )->values();
    }

    /**
     * Remove all transactions that are children of the given transaction.
     */
    protected function removeCommittedTransactionsThatAreChildrenOf(DatabaseTransactionRecord $transaction): void
    {
        [$removedTransactions, $this->committedTransactions] = $this->committedTransactions->partition(
            fn ($committed) => $committed->connection == $transaction->connection
                && $committed->parent === $transaction
        );

        // There may be multiple deeply nested transactions that have already committed that we
        // also need to remove. We will recurse down the children of all removed transaction
        // instances until there are no more deeply nested child transactions for removal.
        $removedTransactions->each(
            fn ($transaction) => $this->removeCommittedTransactionsThatAreChildrenOf($transaction)
        );
    }
}
