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

class DatabaseTransactionRecord
{
    /**
     * The name of the database connection.
     *
     * @var string
     */
    public string $connection;

    /**
     * The transaction level.
     *
     * @var int
     */
    public int $level;

    /**
     * The parent instance of this transaction.
     *
     * @var DatabaseTransactionRecord|null
     */
    public ?DatabaseTransactionRecord $parent;

    /**
     * The callbacks that should be executed after committing.
     *
     * @var array
     */
    protected array $callbacks = [];

    /**
     * The callbacks that should be executed after rollback.
     *
     * @var array
     */
    protected array $callbacksForRollback = [];

    /**
     * Create a new database transaction record instance.
     *
     * @param string $connection
     * @param int $level
     * @param DatabaseTransactionRecord|null $parent
     */
    public function __construct(string $connection, int $level, ?DatabaseTransactionRecord $parent = null)
    {
        $this->connection = $connection;
        $this->level = $level;
        $this->parent = $parent;
    }

    /**
     * Register a callback to be executed after committing.
     *
     * @param callable $callback
     */
    public function addCallback(callable $callback): void
    {
        $this->callbacks[] = $callback;
    }

    /**
     * Register a callback to be executed after rollback.
     *
     * @param callable $callback
     */
    public function addCallbackForRollback(callable $callback): void
    {
        $this->callbacksForRollback[] = $callback;
    }

    /**
     * Execute all of the callbacks.
     */
    public function executeCallbacks(): void
    {
        foreach ($this->callbacks as $callback) {
            $callback();
        }
    }

    /**
     * Execute all of the callbacks for rollback.
     */
    public function executeCallbacksForRollback(): void
    {
        foreach ($this->callbacksForRollback as $callback) {
            $callback();
        }
    }

    /**
     * Get all of the callbacks.
     *
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * Get all of the callbacks for rollback.
     *
     * @return array
     */
    public function getCallbacksForRollback(): array
    {
        return $this->callbacksForRollback;
    }
}
