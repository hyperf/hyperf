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

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Rpc\ProtocolManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * {@inheritdoc}
 */
class CoreMiddleware extends \Hyperf\RpcServer\CoreMiddleware
{
    /**
     * @var \Hyperf\Rpc\ProtocolManager
     */
    protected $protocolManager;

    /**
     * @var \Hyperf\Rpc\Contract\DataFormatterInterface
     */
    protected $dataFormatter;

    /**
     * @var \Hyperf\Rpc\Contract\PackerInterface
     */
    protected $packer;

    public function __construct(ContainerInterface $container, string $serverName)
    {
        parent::__construct($container, $serverName);
        $this->protocolManager = $container->get(ProtocolManager::class);
        $protocolName = 'jsonrpc-http';
        $this->dataFormatter = $container->get($this->protocolManager->getDataFormatter($protocolName));
        $this->packer = $container->get($this->protocolManager->getPacker($protocolName));
    }

    protected function transferToResponse($response, ServerRequestInterface $request): ResponseInterface
    {
        return $this->response()
            ->withAddedHeader('content-type', 'application/json')
            ->withBody(new SwooleStream($this->format($response, $request)));
    }

    protected function format($response, ServerRequestInterface $request): string
    {
        if (is_string($response) || is_array($response)) {
            $response = $this->dataFormatter->formatResponse([$request->getAttribute('request_id') ?? '', $response]);
        }
        return $this->packer->pack($response);
    }
}
