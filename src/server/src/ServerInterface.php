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

namespace Hyperf\Server;

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

interface ServerInterface
{
    const SERVER_HTTP = 1;

    const SERVER_WS = 2;

    const SERVER_TCP = 3;

    public function __construct(ContainerInterface $container, StdoutLoggerInterface $logger, EventDispatcherInterface $dispatcher);

    public function init(ServerConfig $config): ServerInterface;

    public function start();
}
