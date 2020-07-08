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
namespace Hyperf\Nacos\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Nacos\Client;
use Hyperf\Nacos\Exception\RuntimeException;
use Hyperf\Nacos\Lib\NacosInstance;
use Hyperf\Nacos\Lib\NacosService;
use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Nacos\ThisInstance;
use Psr\Container\ContainerInterface;

class BootAppConfListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        $config = $this->container->get(ConfigInterface::class);

        if (! $config->get('nacos')) {
            return;
        }

        $instance = $this->container->get(ThisInstance::class);
        $nacosInstance = $this->container->get(NacosInstance::class);
        if (! $nacosInstance->register($instance)) {
            throw new RuntimeException("nacos register instance fail: {$instance}");
        }
        $this->logger->info('nacos register instance success!', compact('instance'));

        $nacosService = $this->container->get(NacosService::class);
        $service = make(ServiceModel::class, [
            'config' => $config->get('nacos.service'),
        ]);
        $exist = $nacosService->detail($service);
        if (! $exist && ! $nacosService->create($service)) {
            throw new RuntimeException("nacos register service fail: {$service}");
        }
        $this->logger->info('nacos register service success!', compact('service'));

        $client = $this->container->get(Client::class);
        $remote_config = $client->pull();
        /** @var \Hyperf\Config\Config $config */
        $config = $this->container->get(ConfigInterface::class);
        $append_node = config('nacos.config_append_node');
        foreach ($remote_config as $key => $conf) {
            $config->set($append_node ? $append_node . '.' . $key : $key, $conf);
        }
    }
}
