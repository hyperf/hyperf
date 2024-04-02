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

namespace Hyperf\Database\PgSQL\Concerns;

use Closure;
use Exception;
use Throwable;

use function Hyperf\Tappable\tap;

trait PostgreSqlSwooleExtManagesTransactions
{
    /**
     * Execute a Closure within a transaction.
     *
     * @throws Exception|Throwable
     */
    public function transaction(Closure $callback, int $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; ++$currentAttempt) {
            $this->beginTransaction();

            // We'll simply execute the given callback within a try / catch block and if we
            // catch any exception we can rollback this transaction so that none of this
            // gets actually persisted to a database or stored in a permanent fashion.
            try {
                return tap($callback($this), function () {
                    $this->commit();
                });
            }

            // If we catch an exception we'll rollback this transaction and try again if we
            // are not out of attempts. If we are out of attempts we will just throw the
            // exception back out and let the developer handle an uncaught exceptions.
            catch (Exception $e) {
                $this->handleTransactionException(
                    $e,
                    $currentAttempt,
                    $attempts
                );
            } catch (Throwable $e) {
                $this->rollBack();

                throw $e;
            }
        }
    }

    /**
     * Start a new database transaction.
     * @throws Exception
     */
    public function beginTransaction(): void
    {
        $this->createTransaction();

        ++$this->transactions;

        $this->fireConnectionEvent('beganTransaction');
    }

    /**
     * Commit the active database transaction.
     */
    public function commit(): void
    {
        if ($this->transactions == 1) {
            $this->getPdo()->query('COMMIT');
        }

        $this->transactions = max(0, $this->transactions - 1);

        $this->fireConnectionEvent('committed');
    }

    /**
     * Rollback the active database transaction.
     *
     * @param null|int $toLevel
     *
     * @throws Exception
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
        } catch (Exception $e) {
            $this->handleRollBackException($e);
        }

        $this->transactions = $toLevel;

        $this->fireConnectionEvent('rollingBack');
    }

    /**
     * Get the number of active transactions.
     */
    public function transactionLevel(): int
    {
        return $this->transactions;
    }

    /**
     * Handle an exception encountered when running a transacted statement.
     *
     * @param Exception $e
     * @param int $currentAttempt
     * @param int $maxAttempts
     *
     * @throws Exception
     */
    protected function handleTransactionException($e, $currentAttempt, $maxAttempts)
    {
        // On a deadlock, MySQL rolls back the entire transaction so we can't just
        // retry the query. We have to throw this exception all the way out and
        // let the developer handle it in another way. We will decrement too.
        if ($this->causedByDeadlock($e)
            && $this->transactions > 1) {
            --$this->transactions;

            throw $e;
        }

        // If there was an exception we will rollback this transaction and then we
        // can check if we have exceeded the maximum attempt count for this and
        // if we haven't we will return and try this query again in our loop.
        $this->rollBack();

        if ($this->causedByDeadlock($e)
            && $currentAttempt < $maxAttempts) {
            return;
        }

        throw $e;
    }

    /**
     * Create a transaction within the database.
     */
    protected function createTransaction()
    {
        if ($this->transactions == 0) {
            $this->reconnectIfMissingConnection();

            try {
                $this->getPdo()->query('BEGIN');
            } catch (Exception $e) {
                $this->handleBeginTransactionException($e);
            }
        } elseif ($this->transactions >= 1 && $this->queryGrammar->supportsSavepoints()) {
            $this->createSavepoint();
        }
    }

    /**
     * Create a save point within the database.
     */
    protected function createSavepoint()
    {
        $this->getPdo()->query(
            $this->queryGrammar->compileSavepoint('trans' . ($this->transactions + 1))
        );
    }

    /**
     * Handle an exception from a transaction beginning.
     *
     * @param Throwable $e
     *
     * @throws Exception
     */
    protected function handleBeginTransactionException($e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();

            $this->getPdo()->query('BEGIN');
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
            $this->getPdo()->query('ROLLBACK');
        } elseif ($this->queryGrammar->supportsSavepoints()) {
            $this->getPdo()->query(
                $this->queryGrammar->compileSavepointRollBack('trans' . ($toLevel + 1))
            );
        }
    }

    /**
     * Handle an exception from a rollback.
     *
     * @param Exception $e
     *
     * @throws Exception
     */
    protected function handleRollBackException($e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->transactions = 0;
        }

        throw $e;
    }
}
