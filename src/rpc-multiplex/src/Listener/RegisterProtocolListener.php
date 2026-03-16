<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcMultiplex\Listener;

use Hyperf\Codec\Packer\PhpSerializerPacker;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcMultiplex\Constant;
use Hyperf\RpcMultiplex\DataFormatter;
use Hyperf\RpcMultiplex\Packer\JsonPacker;
use Hyperf\RpcMultiplex\PathGenerator;
use Hyperf\RpcMultiplex\Transporter;

class RegisterProtocolListener implements ListenerInterface
{
    public function __construct(private ProtocolManager $protocolManager)
    {
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
     */
    public function process(object $event): void
    {
        $this->protocolManager->register(Constant::PROTOCOL_DEFAULT, [
            'packer' => JsonPacker::class,
            'transporter' => Transporter::class,
            'path-generator' => PathGenerator::class,
            'data-formatter' => DataFormatter::class,
        ]);

        $this->protocolManager->register(Constant::PROTOCOL_PHP_SERIALIZE, [
            'packer' => PhpSerializerPacker::class,
            'transporter' => Transporter::class,
            'path-generator' => PathGenerator::class,
            'data-formatter' => DataFormatter::class,
        ]);
    }
}
