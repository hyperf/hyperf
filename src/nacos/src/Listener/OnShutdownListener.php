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
namespace Hyperf\Nacos\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Nacos\Api\NacosInstance;
use Hyperf\Nacos\Api\NacosService;
use Hyperf\Nacos\Contract\LoggerInterface;
use Hyperf\Nacos\Instance;
use Hyperf\Nacos\Service;
use Psr\Container\ContainerInterface;

class OnShutdownListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            OnShutdown::class,
        ];
    }

    public function process(object $event)
    {
        $config = $this->container->get(ConfigInterface::class);
        if (! $config->get('nacos.remove_node_when_server_shutdown', false)) {
            return;
        }

        $logger = $this->container->get(LoggerInterface::class);
        /** @var NacosService $nacosService */
        $nacosService = $this->container->get(NacosService::class);
        $service = $this->container->get(Service::class);
        $deleted = $nacosService->delete($service);

        if ($deleted) {
            $logger && $logger->info('nacos service delete success.');
        } else {
            $logger && $logger->erro('nacos service delete fail when shutdown.');
        }

        $instance = $this->container->get(Instance::class);
        /** @var NacosInstance $nacosInstance */
        $nacosInstance = make(NacosInstance::class);
        if ($nacosInstance->delete($instance)) {
            $logger && $logger->info('nacos instance delete success.');
        } else {
            $logger && $logger->erro('nacos instance delete fail when shutdown.');
        }
    }
}
