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
namespace Hyperf\GrpcClient\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Grpc\PathGenerator;
use Hyperf\GrpcClient\DataFormatter;
use Hyperf\GrpcClient\GrpcNormalizer;
use Hyperf\GrpcClient\GrpcPacker;
use Hyperf\GrpcClient\GrpcTransporter;
use Hyperf\Rpc\ProtocolManager;

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
        $this->protocolManager->register('grpc', [
            'packer' => GrpcPacker::class,
            'transporter' => GrpcTransporter::class,
            'path-generator' => PathGenerator::class,
            'data-formatter' => DataFormatter::class,
            'normalizer' => GrpcNormalizer::class,
        ]);
    }
}
