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
        $this->responseBuilder = make(ResponseBuilder::class, [
            'dataFormatter' => $this->dataFormatter,
            'packer' => $this->packer,
        ]);
    }

    protected function handleNotFound(ServerRequestInterface $request)
    {
        // @TODO Allow more health check conditions.
        if ($request->getHeaderLine('user-agent') === 'Consul Health Check') {
            // The request that from health checker, return 200 directly.
            return $this->response()->withStatus(200);
        }
        return parent::handleNotFound($request);
    }
}
