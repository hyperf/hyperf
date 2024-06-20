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

namespace Hyperf\GrpcServer\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\RpcServer\Event\AfterPathRegister;
use Hyperf\ServiceGovernance\ServiceManager;
use Psr\Container\ContainerInterface;

class RegisterServiceListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            AfterPathRegister::class,
        ];
    }

    /**
     * All official rpc protocols should register in here,
     * and the others non-official protocols should register in their own component via listener.
     *
     * @param AfterPathRegister $event
     */
    public function process(object $event): void
    {
        if ($this->container->has(ServiceManager::class)) {
            $annotation = $event->annotation;
            if (! in_array($annotation->protocol, ['grpc'])) {
                return;
            }

            $manager = $this->container->get(ServiceManager::class);

            $metadata = $event->toArray();
            $annotationArray = $metadata['annotation'];
            unset($metadata['path'], $metadata['annotation'], $annotationArray['name']);
            $manager->register($annotation->name, $event->path, array_merge($metadata, $annotationArray));
        }
    }
}
