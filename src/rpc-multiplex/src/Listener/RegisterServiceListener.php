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

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\RpcMultiplex\Constant;
use Hyperf\RpcServer\Event\AfterPathRegister;
use Hyperf\ServiceGovernance\ServiceManager;

class RegisterServiceListener implements ListenerInterface
{
    public function __construct(private ServiceManager $serviceManager)
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
        $annotation = $event->annotation;
        if (! in_array($annotation->protocol, $this->getProtocols(), true)) {
            return;
        }
        $metadata = $event->toArray();
        $annotationArray = $metadata['annotation'];
        unset($metadata['path'], $metadata['annotation'], $annotationArray['name']);
        $this->serviceManager->register($annotation->name, $event->path, array_merge($metadata, $annotationArray));
    }

    protected function getProtocols(): array
    {
        return [
            Constant::PROTOCOL_DEFAULT,
        ];
    }
}
