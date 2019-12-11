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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Connection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Utils\Arr;

abstract class AbstractConnection extends Connection implements ConnectionInterface
{
    use DetectsLostConnections;
    use ManagesTransactions;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $readConfig = [];

    /**
     * Indicates if changes have been made to the database.
     *
     * @var bool
     */
    protected $recordsModified = false;

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get the database connection name.
     *
     * @return null|string
     */
    public function getName()
    {
        return Arr::get($this->config, 'name');
    }

    public function release(): void
    {
        if ($this->transactionLevel() > 0) {
            $this->rollBack(0);
            if ($this->container->has(StdoutLoggerInterface::class)) {
                $logger = $this->container->get(StdoutLoggerInterface::class);
                $logger->error('Maybe you\'ve forgotten to commit or rollback the MySQL transaction.');
            }
        }
        $this->pool->release($this);
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

    public function retry(\Throwable $throwable, $name, $arguments)
    {
        if ($this->causedByLostConnection($throwable)) {
            try {
                $this->reconnect();
                return $this->{$name}(...$arguments);
            } catch (\Throwable $throwable) {
                if ($this->container->has(StdoutLoggerInterface::class)) {
                    $logger = $this->container->get(StdoutLoggerInterface::class);
                    $logger->error('Connection execute retry failed. message = ' . $throwable->getMessage());
                }
            }
        }

        throw $throwable;
    }

    /**
     * Reset $recordsModified property to false.
     */
    public function resetRecordsModified(): void
    {
        $this->recordsModified = false;
    }

    /**
     * Indicate if any records have been modified.
     */
    public function recordsHaveBeenModified(bool $value = true)
    {
        if (! $this->recordsModified) {
            $this->recordsModified = $value;
        }
    }

    /**
     * get records modified.
     */
    public function getRecordsModified(): bool
    {
        return $this->recordsModified && Arr::get($this->config, 'sticky');
    }

    /**
     * Get the elapsed time since a given starting point.
     *
     * @param int $start
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Parse the hosts configuration item into an array.
     *
     * @return array
     */
    protected function parseHosts(array $config)
    {
        $hosts = Arr::wrap($config['host']);

        if (empty($hosts)) {
            throw new \InvalidArgumentException('Database hosts array is empty.');
        }

        return $hosts;
    }

    /**
     * Init read write config.
     */
    protected function initReadWriteConfig(array $config)
    {
        if (isset($config['read'])) {
            $this->readConfig = $this->getReadConfig($config);
        }
        if (isset($config['write'])) {
            $this->config = $this->getWriteConfig($config);
        }
    }

    /**
     * Get a read / write level configuration.
     *
     * @param string $type
     * @return array
     */
    protected function getReadWriteConfig(array $config, $type)
    {
        return isset($config[$type][0])
            ? Arr::random($config[$type])
            : $config[$type];
    }

    /**
     * Merge a configuration for a read / write connection.
     *
     * @return array
     */
    protected function mergeReadWriteConfig(array $config, array $merge)
    {
        return Arr::except(array_merge($config, $merge), ['read', 'write']);
    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @return array
     */
    protected function getReadConfig(array $config)
    {
        return $this->mergeReadWriteConfig(
            $config,
            $this->getReadWriteConfig($config, 'read')
        );
    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @return array
     */
    protected function getWriteConfig(array $config)
    {
        return $this->mergeReadWriteConfig(
            $config,
            $this->getReadWriteConfig($config, 'write')
        );
    }
}
