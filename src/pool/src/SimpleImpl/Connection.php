<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Pool\SimpleImpl;

use Hyperf\Pool\Connection as AbstractConnection;

class Connection extends AbstractConnection
{
    /**
     * @var callable
     */
    public $callback;

    public $connection;

    public function __construct(ContainerInterface $container, Pool $pool, callable $callback)
    {
        $this->callback = $callback;
        parent::__construct($container, $pool);
    }

    public function getActiveConnection()
    {
        if (! $this->check()) {
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
        return true;
    }
}
