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
namespace Hyperf\Nacos\Service\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Nacos\Api\NacosInstance;
use Hyperf\Nacos\Api\NacosService;
use Hyperf\Nacos\Contract\LoggerInterface;
use Hyperf\Nacos\Service\Instance;
use Hyperf\Nacos\Service\Service;
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
        if (! $config->get('nacos.service.remove_node_when_server_shutdown', false)) {
            return;
        }

        $logger = $this->container->get(LoggerInterface::class);

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
