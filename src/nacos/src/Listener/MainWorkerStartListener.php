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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Nacos\Api\NacosInstance;
use Hyperf\Nacos\Api\NacosService;
use Hyperf\Nacos\Client;
use Hyperf\Nacos\Exception\RuntimeException;
use Hyperf\Nacos\Instance;
use Hyperf\Nacos\Service;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        $config = $this->container->get(ConfigInterface::class);

        if (! $config->get('nacos')) {
            return;
        }

        $instance = $this->container->get(Instance::class);
        $nacosInstance = $this->container->get(NacosInstance::class);
        if (! $nacosInstance->register($instance)) {
            throw new RuntimeException(sprintf('nacos register instance fail: %s', $instance));
        }
        $this->logger->info('nacos register instance success.', compact('instance'));

        $nacosService = $this->container->get(NacosService::class);
        $service = $this->container->get(Service::class);
        $exist = $nacosService->detail($service);
        if (! $exist && ! $nacosService->create($service)) {
            throw new RuntimeException(sprintf('nacos register service fail: %s', $service));
        }
        $this->logger->info('nacos register service success.', compact('service'));

        $client = $this->container->get(Client::class);
        $config = $this->container->get(ConfigInterface::class);
        $appendNode = $config->get('nacos.config_append_node');
        foreach ($client->pull() as $key => $conf) {
            $config->set($appendNode ? $appendNode . '.' . $key : $key, $conf);
        }
    }
}
