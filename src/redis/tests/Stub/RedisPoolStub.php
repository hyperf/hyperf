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

namespace HyperfTest\Redis\Stub;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Redis\Pool\RedisPool;
use Throwable;

class RedisPoolStub extends RedisPool
{
    public function flushAll()
    {
        while ($conn = $this->channel->pop(0.001)) {
            try {
                $conn->close();
            } catch (Throwable $exception) {
                if ($this->container->has(StdoutLoggerInterface::class) && $logger = $this->container->get(StdoutLoggerInterface::class)) {
                    $logger->error((string) $exception);
                }
            } finally {
                --$this->currentConnections;
            }
        }
    }

    protected function createConnection(): ConnectionInterface
    {
        return new RedisConnectionStub($this->container, $this, $this->config);
    }
}
