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
namespace Hyperf\ConfigApollo\Listener;

use Hyperf\Command\Event\BeforeHandle;
use Hyperf\ConfigApollo\Option;
use Hyperf\ConfigApollo\PipeMessage;
use Hyperf\ConfigApollo\ReleaseKey;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;

class BootProcessListener extends OnPipeMessageListener
{
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
        if (! $this->config->get('apollo.enable', false)) {
            return;
        }

        $ipcCallback = function ($configs, $namespace) {
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
        };

        $callbacks = [];
        $namespaces = $this->config->get('apollo.namespaces', []);
        foreach ($namespaces as $namespace) {
            if (is_string($namespace)) {
                $callbacks[$namespace] = $ipcCallback;
            }
        }
        $this->client->pull($namespaces, $callbacks);

        if (! $this->config->get('apollo.use_standalone_process', true)) {
            Coroutine::create(function () use ($namespaces, $callbacks) {
                $interval = $this->config->get('apollo.interval', 5);
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
}
