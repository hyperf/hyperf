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

namespace HyperfTest\Pool\Stub;

use Hyperf\Context\Context;
use Hyperf\Coordinator\Timer;
use Hyperf\Pool\KeepaliveConnection;

class KeepaliveConnectionStub extends KeepaliveConnection
{
    public Timer $timer;

    protected $activeConnection;

    public function setActiveConnection($connection)
    {
        $this->activeConnection = $connection;
    }

    protected function getActiveConnection()
    {
        return $this->activeConnection;
    }

    protected function sendClose($connection): void
    {
        $data = Context::get('test.pool.heartbeat_connection', []);
        $data['close'] = 'close protocol';
        Context::set('test.pool.heartbeat_connection', $data);
    }

    protected function heartbeat(): void
    {
        $data = Context::get('test.pool.heartbeat_connection', []);
        $data['heartbeat'] = 'heartbeat protocol';
        Context::set('test.pool.heartbeat_connection', $data);
    }
}
