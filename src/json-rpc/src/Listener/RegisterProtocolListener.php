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

namespace Hyperf\JsonRpc\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\JsonRpc\DataFormatter;
use Hyperf\JsonRpc\JsonRpcHttpTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;
use Hyperf\JsonRpc\Packer\JsonRpcPacker;
use Hyperf\JsonRpc\PathGenerator;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\Utils\Packer\JsonPacker;

class RegisterProtocolListener implements ListenerInterface
{
    /**
     * @var ProtocolManager
     */
    private $protocolManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ProtocolManager $protocolManager, ConfigInterface $config)
    {
        $this->protocolManager = $protocolManager;
        $this->config = $config;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
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
        $transporter = $this->config->get('json_rpc.transporter.tcp.class', JsonRpcTransporter::class);

        $this->protocolManager->register('jsonrpc', [
            'packer' => JsonRpcPacker::class,
            'transporter' => $transporter,
            'path-generator' => PathGenerator::class,
            'data-formatter' => DataFormatter::class,
        ]);

        $this->protocolManager->register('jsonrpc-http', [
            'packer' => JsonPacker::class,
            'transporter' => JsonRpcHttpTransporter::class,
            'path-generator' => PathGenerator::class,
            'data-formatter' => DataFormatter::class,
        ]);
    }
}
