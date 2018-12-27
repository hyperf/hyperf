<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Framework\Bootstrap;

use Hyperf\Framework\Constants\SwooleEvent;
use Hyperf\Framework\Server;
use Hyperf\Memory;
use Psr\Container\ContainerInterface;
use Swoole\Server as SwooleServer;

class ServerStartCallback
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Server
     */
    private $server;

    public function __construct(ContainerInterface $container, Server $server)
    {
        $this->container = $container;
        $this->server = $server;
    }

    public function onStart(SwooleServer $server)
    {
        Memory\LockManager::initialize(SwooleEvent::ON_WORKER_START, SWOOLE_RWLOCK, 'workerStart');
        Memory\AtomicManager::initialize(SwooleEvent::ON_WORKER_START);
    }
}
