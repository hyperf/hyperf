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

namespace Hyperf\RpcServer\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeServerStart;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\RpcClient\Transporter\JsonRpcTransporter;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\Utils\Packer\JsonPacker;

/**
 * @Listener
 */
class RegisterProtocolListener implements ListenerInterface
{
    /**
     * @var ProtocolManager
     */
    private $protocolManager;

    public function __construct(ProtocolManager $protocolManager)
    {
        $this->protocolManager = $protocolManager;
    }

    public function listen(): array
    {
        return [
            BeforeServerStart::class,
        ];
    }

    /**
     * All official rpc protocols should register in here,
     * and the others non-official protocols should register in their own component via listener.
     *
     * @param BeforeWorkerStart $event
     */
    public function process(object $event)
    {
        $this->protocolManager->register('jsonrpc-2.0', [
            'packer' => JsonPacker::class,
            'transporter' => JsonRpcTransporter::class,
        ]);
    }
}
