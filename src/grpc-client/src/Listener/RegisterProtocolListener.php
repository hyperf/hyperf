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
use Psr\Container\ContainerInterface;

class RegisterProtocolListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
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
        if ($this->container->has(ProtocolManager::class)) {
            $manager = $this->container->get(ProtocolManager::class);
            $manager->register('grpc', [
                'packer' => GrpcPacker::class,
                'transporter' => GrpcTransporter::class,
                'path-generator' => PathGenerator::class,
                'data-formatter' => DataFormatter::class,
                'normalizer' => GrpcNormalizer::class,
            ]);
        }
    }
}
