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

namespace Hyperf\JsonRpc;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\CoreMiddleware;
use Hyperf\RpcServer\RequestDispatcher;
use Psr\Container\ContainerInterface;

class ServerFactory
{
    protected $coreMiddleware = CoreMiddleware::class;

    public function __invoke(ContainerInterface $container): Server
    {
        $dispatcher = $container->get(RequestDispatcher::class);
        $logger = $container->get(StdoutLoggerInterface::class);
        $protocolManager = $container->get(ProtocolManager::class);
        return new Server('jsonrpc', $this->coreMiddleware, $container, $dispatcher, $logger, $protocolManager);
    }
}
