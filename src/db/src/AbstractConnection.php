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
use Hyperf\DB\Events\QueryExecuted;
use Hyperf\Pool\Connection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

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

    /**
     * The event dispatcher instance.
     *
     * @var EventDispatcherInterface
     */
    protected $events;

    public function __construct(ContainerInterface $container, Pool $pool)
    {
        parent::__construct($container, $pool);
        if ($this->container->has(EventDispatcherInterface::class)) {
            $this->setEventDispatcher($this->container->get(EventDispatcherInterface::class));
        }
    }

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
     * Get the event dispatcher used by the connection.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance on the connection.
     *
     * @return $this
     */
    public function setEventDispatcher(EventDispatcherInterface $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Unset the event dispatcher for this connection.
     */
    public function unsetEventDispatcher()
    {
        $this->events = null;
    }

    /**
     * Log a query in the connection's query log.
     *
     * @param string $query
     * @param array $bindings
     * @param null|float $time
     */
    public function logQuery($query, $bindings = [], $time = null)
    {
        $this->event(new QueryExecuted($query, $bindings, $time, $this));
    }

    /**
     * Indicate if any records have been modified.
     */
    public function recordsHaveBeenModified(bool $value = true)
    {
        if ($this->recordsModified != $value) {
            $this->recordsModified = $value;
        }
    }

    /**
     * Fire the given event if possible.
     * @param mixed $event
     */
    protected function event($event)
    {
        if (isset($this->events)) {
            $this->events->dispatch($event);
        }
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
            throw new InvalidArgumentException('Database hosts array is empty.');
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
