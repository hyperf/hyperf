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

namespace Hyperf\Pool\SimplePool;

use Hyperf\Pool\Connection as AbstractConnection;
use Psr\Container\ContainerInterface;

class Connection extends AbstractConnection
{
    /**
     * @var callable
     */
    protected $callback;

    protected mixed $connection = null;

    public function __construct(ContainerInterface $container, Pool $pool, callable $callback)
    {
        $this->callback = $callback;
        parent::__construct($container, $pool);
    }

    public function getActiveConnection()
    {
        if (! $this->connection || ! $this->check()) {
            $this->reconnect();
        }

        return $this->connection;
    }

    public function reconnect(): bool
    {
        $this->connection = ($this->callback)();
        $this->lastUseTime = microtime(true);
        return true;
    }

    public function close(): bool
    {
        $this->connection = null;
        return true;
    }
}
