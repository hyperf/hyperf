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

namespace Hyperf\DB;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Connection;
use Hyperf\Pool\Exception\ConnectionException;
use Throwable;

abstract class AbstractConnection extends Connection implements ConnectionInterface
{
    use DetectsLostConnections;
    use ManagesTransactions;

    protected array $config = [];

    public function getConfig(): array
    {
        return $this->config;
    }

    public function release(): void
    {
        try {
            if ($this->transactionLevel() > 0) {
                $this->rollBack(0);
                if ($this->container->has(StdoutLoggerInterface::class)) {
                    $logger = $this->container->get(StdoutLoggerInterface::class);
                    $logger->error('Maybe you\'ve forgotten to commit or rollback the MySQL transaction.');
                }
            }

            parent::release();
        } catch (Throwable $exception) {
            if ($this->container->has(StdoutLoggerInterface::class) && $logger = $this->container->get(StdoutLoggerInterface::class)) {
                $logger->critical('Release connection failed, caused by ' . $exception);
            }
            throw $exception;
        }
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    public function retry(Throwable $throwable, $name, $arguments)
    {
        if ($this->transactionLevel() > 0) {
            throw $throwable;
        }

        if ($this->causedByLostConnection($throwable)) {
            try {
                $this->reconnect();
                return $this->{$name}(...$arguments);
            } catch (Throwable $throwable) {
                if ($this->container->has(StdoutLoggerInterface::class)) {
                    $logger = $this->container->get(StdoutLoggerInterface::class);
                    $logger->error('Connection execute retry failed. message = ' . $throwable->getMessage());
                }
            }
        }

        throw $throwable;
    }
}
