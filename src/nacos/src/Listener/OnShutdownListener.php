<?php

namespace Hyperf\Nacos\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Nacos\Lib\NacosInstance;
use Hyperf\Nacos\Lib\NacosService;
use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Nacos\ThisInstance;

class OnShutdownListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            OnShutdown::class,
        ];
    }

    public function process(object $event)
    {
        if (!config('nacos')) {
            return;
        }

        if (!config('nacos.deleteServiceWhenShutdown', false)) {
            return;
        }

        $logger = container(LoggerFactory::class)->get('nacos');
        /** @var NacosService $nacos_service */
        $nacos_service = container(NacosService::class);
        /** @var ServiceModel $service */
        $service = make(ServiceModel::class, ['config' => config('nacos.service')]);
        $deleted = $nacos_service->delete($service);

        if ($deleted) {
            $logger->info('nacos service delete success!');
        } else {
            $logger->erro('nacos service delete fail when shutdown!');
        }

        /** @var ThisInstance $instance */
        $instance = make(ThisInstance::class);
        /** @var NacosInstance $nacos_instance */
        $nacos_instance = make(NacosInstance::class);
        $deleted_instance = $nacos_instance->delete($instance);

        if ($deleted_instance) {
            $logger->info('nacos instance delete success!');
        } else {
            $logger->erro('nacos instance delete fail when shutdown!');
        }
    }
}
