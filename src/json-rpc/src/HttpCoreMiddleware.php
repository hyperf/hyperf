<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\JsonRpc;

use Hyperf\Rpc\ProtocolManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * {@inheritdoc}
 */
class HttpCoreMiddleware extends CoreMiddleware
{
    public function __construct(ContainerInterface $container, string $serverName)
    {
        parent::__construct($container, $serverName);
        $this->protocolManager = $container->get(ProtocolManager::class);
        $protocolName = 'jsonrpc-http';
        $this->dataFormatter = $container->get($this->protocolManager->getDataFormatter($protocolName));
        $this->packer = $container->get($this->protocolManager->getPacker($protocolName));
    }
}
