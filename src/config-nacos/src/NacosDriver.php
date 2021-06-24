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
namespace Hyperf\ConfigNacos;

use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\ConfigNacos\Config\PipeMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessCollector;
use Hyperf\Process\ProcessManager;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coordinator\Constants as CooConst;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;

class NacosDriver extends AbstractDriver
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(Client $client, StdoutLoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function configFetcherHandle(): void
    {
        $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
        $cache = [];
        while (ProcessManager::isRunning()) {
            $config = $this->client->pull();
            if ($config != $cache) {
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

                $cache = $config;
            }
            sleep($this->config->get('config_center.drivers.nacos.interval', 5));
        }
    }

    public function bootProcessHandle(object $event): void
    {
        if ($config = $this->client->pull()) {
            $this->updateConfig($config);
        }

        if (! $this->config->get('config_center.use_standalone_process', true)) {
            Coroutine::create(function () {
                $interval = $this->config->get('config_center.drivers.nacos.interval', 5);
                retry(INF, function () use ($interval) {
                    $prevConfig = [];
                    while (true) {
                        $coordinator = CoordinatorManager::until(CooConst::WORKER_EXIT);
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
        $root = $this->config->get('config_center.drivers.nacos.default_key');
        foreach ($config ?? [] as $key => $conf) {
            if (is_int($key)) {
                $key = $root;
            }
            if (is_array($conf) && $this->config->get('config_center.drivers.nacos.merge_mode') === Constants::CONFIG_MERGE_APPEND) {
                $conf = Arr::merge($this->config->get($key, []), $conf);
            }

            $this->config->set($key, $conf);
        }
    }
}
