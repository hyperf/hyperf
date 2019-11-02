<?php
declare(strict_types=1);

namespace Hyperf\DB;


use Exception;
use Hyperf\DB\Exception\QueryException;
use Hyperf\Utils\Context;

class TransactionManager
{


    /**
     * @var string
     */
    protected $poolName = 'default';

    public function beginTransaction()
    {
        $this->createTransaction();
        Context::set('transactions', (int)Context::get('transactions') + 1);
    }


    public function rollback(?int $toLevel = null)
    {
        $transactions = Context::get('transactions');
        $toLevel = is_null($toLevel)
            ? $transactions - 1
            : $toLevel;

        if ($toLevel < 0 || $toLevel >= $transactions) {
            return;
        }

        $this->performRollBack($toLevel);
        Context::set('transactions', $toLevel);
    }

    public function commit()
    {
        $transactions = Context::get('transactions');
        if ($transactions == 1) {
            $connection = Context::get($this->getContextKey());
            $connection->commit();
            Context::set('transactions', 0);
        }

        Context::set('transactions', max(0, $transactions - 1));

    }

    /**
     * Create a transaction within the database.
     *
     * @return void
     */
    protected function createTransaction()
    {
        $transactions = Context::get('transactions');
        if (!Context::has('transactions') || $transactions == 0) {
            try {
                $connection = Context::get($this->getContextKey());
                $connection->beginTransaction();
            } catch (Exception $e) {
                throw new QueryException('begin transaction fail!');
            }
        } elseif ($transactions >= 1) {
            $this->createSavepoint();
        }
    }

    protected function createSavepoint()
    {
        $connection = Context::get($this->getContextKey());
        $data = [];
        $connection->prepare($this->compileSavepoint('trans' . (Context::get('transactions') + 1)), $data);
    }


    protected function performRollBack(?int $toLevel = null)
    {
        $connection = Context::get($this->getContextKey());
        if ($toLevel == 0) {
            $connection->rollBack();
            Context::set($this->getContextKey(), null);
        } else {
            $connection->prepare($this->compileSavepointRollBack('trans' . ($toLevel + 1)));
        }
    }

    protected function compileSavepoint($name)
    {
        return 'SAVEPOINT ' . $name;
    }

    /**
     * Compile the SQL statement to execute a savepoint rollback.
     *
     * @return string
     */
    public function compileSavepointRollBack($name)
    {
        return 'ROLLBACK TO SAVEPOINT ' . $name;
    }

    /**
     * The key to identify the connection object in coroutine context.
     */
    private function getContextKey(): string
    {
        return sprintf('database.%s', $this->poolName);
    }
}