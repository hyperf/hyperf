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
namespace Hyperf\ConfigAliyunAcm;

use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessCollector;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;

class AliyunAcmDriver extends AbstractDriver
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ClientInterface $client, StdoutLoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
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

                $processes = ProcessCollector::all();
                if ($processes) {
                    $string = serialize($pipeMessage);
                    /** @var \Swoole\Process $process */
                    foreach ($processes as $process) {
                        $result = $process->exportSocket()->send($string, 10);
                        if ($result === false) {
                            $this->logger->error('Configuration synchronization failed. Please restart the server.');
                        }
                    }
                }
            }

            sleep($this->config->get('config_center.drivers.aliyun_acm.interval', 5));
        }
    }

    public function bootProcessHandle(object $event): void
    {
        if ($config = $this->client->pull()) {
            $this->updateConfig($config);
        }

        if (! $this->config->get('config_center.use_standalone_process', true)) {
            Coroutine::create(function () {
                $interval = $this->config->get('config_center.drivers.aliyun_acm.interval', 5);
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
            foreach ($event->data->data ?? [] as $key => $value) {
                $this->config->set($key, $value);
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
        }
    }

    protected function updateConfig(array $config)
    {
        foreach ($config as $key => $value) {
            if (is_string($key)) {
                $this->config->set($key, $value);
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
        }
    }
}
