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
namespace Hyperf\ConfigApollo;

use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessCollector;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;

class ApolloDriver extends AbstractDriver
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ClientInterface $client, StdoutLoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function configFetcherHandle(): void
    {
        if (! $this->server) {
            return;
        }
        [$namespaces, $callbacks] = $this->buildNamespacesCallbacks(function ($configs, $namespace) {
            if (isset($configs['configurations'], $configs['releaseKey'])) {
                $configs['namespace'] = $namespace;
                $pipeMessage = new PipeMessage($configs);
                $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
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
        });
        while (true) {
            $this->client->pull($namespaces, $callbacks);
            sleep($this->config->get('config_center.drivers.apollo.interval', 5));
        }
    }

    public function bootProcessHandle(object $event): void
    {
        [$namespaces, $callbacks] = $this->buildNamespacesCallbacks(function ($configs, $namespace) {
            if (isset($configs['configurations'], $configs['releaseKey'])) {
                $configs['namespace'] = $namespace;
                $data = new PipeMessage($configs);

                $option = $this->client->getOption();
                if (! $option instanceof Option) {
                    return;
                }
                $cacheKey = $option->buildCacheKey($data->namespace);
                $cachedKey = ReleaseKey::get($cacheKey);
                if ($cachedKey && $cachedKey === $data->releaseKey) {
                    return;
                }
                foreach ($data->configurations ?? [] as $key => $value) {
                    $this->config->set($key, $this->formatValue($value));
                    $this->logger->debug(sprintf('Config [%s] is updated', $key));
                }
                ReleaseKey::set($cacheKey, $data->releaseKey);
            }
        });
        $this->client->pull($namespaces, $callbacks);

        if (! $this->config->get('config_center.use_standalone_process', true)) {
            Coroutine::create(function () use ($namespaces, $callbacks) {
                $interval = $this->config->get('config_center.drivers.apollo.interval', 5);
                retry(INF, function () use ($namespaces, $callbacks, $interval) {
                    while (true) {
                        $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                        $workerExited = $coordinator->yield($interval);
                        if ($workerExited) {
                            break;
                        }
                        $this->client->pull($namespaces, $callbacks);
                    }
                }, $interval * 1000);
            });
        }
    }

    public function onPipeMessageHandle(object $event): void
    {
        if (property_exists($event, 'data') && $event->data instanceof PipeMessage) {
            /** @var PipeMessage $data */
            $data = $event->data;

            if (! $data->isValid()) {
                return;
            }

            $option = $this->client->getOption();
            if (! $option instanceof Option) {
                return;
            }
            $cacheKey = $option->buildCacheKey($data->namespace);
            $cachedKey = ReleaseKey::get($cacheKey);
            if ($cachedKey && $cachedKey === $data->releaseKey) {
                return;
            }
            foreach ($data->configurations ?? [] as $key => $value) {
                $this->config->set($key, $this->formatValue($value));
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
            ReleaseKey::set($cacheKey, $data->releaseKey);
        }
    }

    protected function formatValue($value)
    {
        if (! $this->config->get('config_center.drivers.apollo.strict_mode', false)) {
            return $value;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (is_numeric($value)) {
            $value = (strpos($value, '.') === false) ? (int) $value : (float) $value;
        }

        return $value;
    }

    protected function buildNamespacesCallbacks(callable $ipcCallback): array
    {
        $callbacks = [];
        $namespaces = $this->config->get('config_center.drivers.apollo.namespaces', []);
        foreach ($namespaces as $namespace) {
            if (is_string($namespace)) {
                $callbacks[$namespace] = $ipcCallback;
            }
        }
        return [$namespaces, $callbacks];
    }
}
