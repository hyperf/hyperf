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
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Nacos\Lib\NacosInstance;
use Hyperf\Nacos\Lib\NacosService;
use Hyperf\Nacos\ThisInstance;
use Hyperf\Nacos\ThisService;
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
        if (! $config->get('nacos.delete_service_when_shutdown', false)) {
            return;
        }

        $logger = $this->container->get(LoggerFactory::class)->get('nacos');
        /** @var NacosService $nacosService */
        $nacosService = $this->container->get(NacosService::class);
        $service = $this->container->get(ThisService::class);
        $deleted = $nacosService->delete($service);

        if ($deleted) {
            $logger->info('nacos service delete success!');
        } else {
            $logger->erro('nacos service delete fail when shutdown!');
        }

        $instance = $this->container->get(ThisInstance::class);
        /** @var NacosInstance $nacosInstance */
        $nacosInstance = make(NacosInstance::class);
        if ($nacosInstance->delete($instance)) {
            $logger->info('nacos instance delete success!');
        } else {
            $logger->erro('nacos instance delete fail when shutdown!');
        }
    }
}
