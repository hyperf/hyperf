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

namespace Hyperf\Server;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

interface ServerInterface
{
    const SERVER_HTTP = 1;

    const SERVER_WEBSOCKET = 2;

    const SERVER_BASE = 3;

    public function __construct(ContainerInterface $container, LoggerInterface $logger, EventDispatcherInterface $dispatcher);

    public function init(ServerConfig $config): ServerInterface;

    public function start();

    public function getServer(): \Swoole\Server;
}
