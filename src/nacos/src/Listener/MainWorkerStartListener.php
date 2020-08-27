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
use Hyperf\Nacos\Constants;
use Hyperf\Nacos\Exception\RuntimeException;
use Hyperf\Nacos\Instance;
use Hyperf\Nacos\Service;
use Hyperf\Utils\Arr;
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
        if (! $config->get('nacos.enable', true)) {
            return;
        }

        try {
            $nacosService = $this->container->get(NacosService::class);
            $service = $this->container->get(Service::class);
            $exist = $nacosService->detail($service);
            if (! $exist && ! $nacosService->create($service)) {
                throw new RuntimeException(sprintf('nacos register service fail: %s', $service));
            }
            $this->logger->info('nacos register service success.', compact('service'));

            $instance = $this->container->get(Instance::class);
            $nacosInstance = $this->container->get(NacosInstance::class);
            if (! $nacosInstance->register($instance)) {
                throw new RuntimeException(sprintf('nacos register instance fail: %s', $instance));
            }
            $this->logger->info('nacos register instance success.', compact('instance'));

            $client = $this->container->get(Client::class);
            $config = $this->container->get(ConfigInterface::class);
            $appendNode = $config->get('nacos.config_append_node');

            foreach ($client->pull() as $key => $conf) {
                $configKey = $appendNode ? $appendNode . '.' . $key : $key;
                if (is_array($conf) && $config->get('nacos.config_merge_mode') == Constants::CONFIG_MERGE_APPEND) {
                    $conf = Arr::merge($config->get($configKey, []), $conf);
                }
                $config->set($configKey, $conf);
            }
        } catch (\Throwable $exception) {
            $this->logger->critical((string) $exception);
        }
    }
}
