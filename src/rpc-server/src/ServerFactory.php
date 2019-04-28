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

namespace Hyperf\RpcServer;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Rpc\Contract\PackerInterface;
use Psr\Container\ContainerInterface;

class ServerFactory
{
    protected $coreMiddleware = CoreMiddleware::class;

    public function __invoke(ContainerInterface $container): Server
    {
        $dispatcher = $container->get(RequestDispatcher::class);
        $packer = $container->get(PackerInterface::class);
        $logger = $container->get(StdoutLoggerInterface::class);
        return new Server('jsonrpc', $this->coreMiddleware, $container, $dispatcher, $packer, $logger);
    }
}
