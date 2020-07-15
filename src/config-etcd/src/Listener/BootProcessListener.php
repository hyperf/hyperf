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
namespace Hyperf\ConfigEtcd\Listener;

use Hyperf\Command\Event\BeforeHandle;
use Hyperf\ConfigEtcd\ClientInterface;
use Hyperf\ConfigEtcd\KV;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Packer\JsonPacker;
use Psr\Container\ContainerInterface;

class BootProcessListener implements ListenerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var PackerInterface
     */
    private $packer;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->client = $container->get(ClientInterface::class);

        $this->mapping = $this->config->get('config_etcd.mapping', []);
        $this->packer = $container->get($this->config->get('config_etcd.packer', JsonPacker::class));
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
            BeforeProcessHandle::class,
            BeforeHandle::class,
        ];
    }

    public function process(object $event)
    {
        if (! $this->config->get('config_etcd.enable', false)) {
            return;
        }

        if ($config = $this->client->pull()) {
            $this->updateConfig($config);
        }

        if (! $this->config->get('config_etcd.use_standalone_process', true)) {
            Coroutine::create(function () {
                $interval = $this->config->get('config_etcd.interval', 5);
                retry(INF, function () use ($interval) {
                    $prevConfig = [];
                    while (true) {
                        $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                        $workerExited = $coordinator->yield($interval);
                        if ($workerExited) {
                            break;
                        }
                        $config = $this->client->pull();
                        if ($config !== $prevConfig) {
                            $this->updateConfig($config);
                        }
                        $prevConfig = $config;
                    }
                }, $interval * 1000);
            });
        }
    }

    protected function updateConfig(array $config)
    {
        $configurations = $this->format($config);
        foreach ($configurations as $kv) {
            $key = $this->mapping[$kv->key] ?? null;
            if (is_string($key)) {
                $this->config->set($key, $this->packer->unpack($kv->value));
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
        }
    }

    /**
     * Format kv configurations.
     */
    protected function format(array $config): array
    {
        $result = [];
        foreach ($config as $value) {
            $result[] = new KV($value);
        }

        return $result;
    }
}
