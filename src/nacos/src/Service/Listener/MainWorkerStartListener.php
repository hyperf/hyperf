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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Nacos\Api\NacosInstance as NacosInstanceApi;
use Hyperf\Nacos\Api\NacosService as NacosServiceApi;
use Hyperf\Nacos\Config\Client;
use Hyperf\Nacos\Exception\RuntimeException;
use Hyperf\Nacos\Service\Instance;
use Hyperf\Nacos\Service\Service;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;

class MainWorkerStartListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event)
    {
        if (! $this->config->get('nacos.service.enable', false)) {
            return;
        }

        $nacosServiceApi = $this->container->get(NacosServiceApi::class);
        $service = $this->container->get(Service::class);
        $exist = $nacosServiceApi->detail($service);
        if (! $exist) {
            if (! $nacosServiceApi->create($service)) {
                throw new RuntimeException(sprintf('nacos register service fail: %s', $service));
            }

            $this->logger->info('nacos register service success.', compact('service'));
        }

        $instance = $this->container->get(Instance::class);
        $nacosInstanceApi = $this->container->get(NacosInstanceApi::class);
        if (! $nacosInstanceApi->register($instance)) {
            throw new RuntimeException(sprintf('nacos register instance fail: %s', $instance));
        }
        $this->logger->info('nacos register instance success.', compact('instance'));
    }
}
