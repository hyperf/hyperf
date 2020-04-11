<?php
namespace Hyperf\Nacos\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Nacos\Lib\NacosInstance;
use Hyperf\Nacos\Lib\NacosService;
use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Nacos\ThisInstance;
use Hyperf\Nacos\Util\RemoteConfig;

class BootAppConfListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        if (!config('nacos')) {
            return;
        }
        $logger = container(LoggerFactory::class)->get('nacos');

        // 注册实例
        /** @var ThisInstance $instance */
        $instance = make(ThisInstance::class);
        /** @var NacosInstance $nacos_instance */
        $nacos_instance = make(NacosInstance::class);
        if (!$nacos_instance->register($instance)) {
            throw new \Exception("nacos register instance fail: {$instance}");
        } else {
            $logger->info('nacos register instance success!', compact('instance'));
        }

        // 注册服务
        /** @var NacosService $nacos_service */
        $nacos_service = container(NacosService::class);
        /** @var ServiceModel $service */
        $service = make(ServiceModel::class, ['config' => config('nacos.service')]);
        $exist = $nacos_service->detail($service);
        if (!$exist && !$nacos_service->create($service)) {
            throw new \Exception("nacos register service fail: {$service}");
        } else {
            $logger->info('nacos register service success!', compact('instance'));
        }

        $remote_config = RemoteConfig::get();
        /** @var \Hyperf\Config\Config $config */
        $config = container(ConfigInterface::class);
        $append_node = config('nacos.configAppendNode');
        foreach ($remote_config as $key => $conf) {
            $config->set($append_node ? $append_node . '.' . $key : $key, $conf);
        }
    }
}
