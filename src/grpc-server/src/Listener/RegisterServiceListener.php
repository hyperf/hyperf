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

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\RpcServer\Annotation\RpcService;
use Hyperf\RpcServer\Event\AfterPathRegister;
use Hyperf\ServiceGovernance\ServiceManager;

class RegisterServiceListener implements ListenerInterface
{
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
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
     * @param AfterPathRegister $event
     */
    public function process(object $event)
    {
        $collector = AnnotationCollector::list();

        foreach ($collector as $className => $metadata) {
            if (isset($metadata['_c'][RpcService::class])) {
                $this->handleRpcService($metadata['_c'][RpcService::class]);
            }
        }
    }

    protected function handleRpcService($annotation)
    {
        if ($annotation->protocol != 'grpc') {
            return;
        }

        $this->serviceManager->register($annotation->name, "default", (array)$annotation);
    }
}
