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
namespace Hyperf\ConfigEtcd;

use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessCollector;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Packer\JsonPacker;
use Psr\Container\ContainerInterface;

class EtcdDriver extends AbstractDriver
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var JsonPacker
     */
    protected $packer;

    /**
     * @var array
     */
    protected $mapping;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->client = $container->get(ClientInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->config = $container->get(ConfigInterface::class);

        $this->mapping = $this->config->get('config_center.drivers.etcd.mapping', []);
        $this->packer = $container->get($this->config->get('config_center.drivers.etcd.packer', JsonPacker::class));
    }

    public function configFetcherHandle(): void
    {
        $cacheConfig = null;
        while (true) {
            $config = $this->client->pull();
            if ($config !== $cacheConfig) {
                $cacheConfig = $config;
                $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
                $pipeMessage = new PipeMessage($config);
                for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                    $this->server->sendMessage($pipeMessage, $workerId);
                }

                $string = serialize($pipeMessage);

                $processes = ProcessCollector::all();
                /** @var \Swoole\Process $process */
                foreach ($processes as $process) {
                    $result = $process->exportSocket()->send($string, 10);
                    if ($result === false) {
                        $this->logger->error('Configuration synchronization failed. Please restart the server.');
                    }
                }
            }

            sleep($this->config->get('config_center.drivers.etcd.interval', 5));
        }
    }

    public function bootProcessHandle(object $event): void
    {
        if ($config = $this->client->pull()) {
            $this->updateConfig($config);
        }

        if (! $this->config->get('config_center.use_standalone_process', true)) {
            Coroutine::create(function () {
                $interval = $this->config->get('config_center.drivers.etcd.interval', 5);
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

    public function onPipeMessageHandle(object $event): void
    {
        if (property_exists($event, 'data') && $event->data instanceof PipeMessage) {
            $this->updateConfig($event->data->configurations);
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
