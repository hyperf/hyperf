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
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class ApolloDriver extends AbstractDriver
{
    /**
     * @var ClientInterface
     */
    protected $client;

    protected $driverName = 'apollop';

    protected $pipeMessage = PipeMessage::class;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
    }

    public function configFetcherHandle(): void
    {
        if (! $this->server) {
            return;
        }
        [$namespaces, $callbacks] = $this->buildNamespacesCallbacks(function ($configs, $namespace) {
            if (isset($configs['configurations'], $configs['releaseKey'])) {
                $configs['namespace'] = $namespace;
                $this->shareConfigToProcesses($configs);
            }
        });
        while (true) {
            $this->client->pull($namespaces, $callbacks);
            sleep($this->getInterval());
        }
    }

    public function createMessageFetcherLoop(): void
    {
        if (! $this->config->get('config_center.use_standalone_process', true)) {
            Coroutine::create(function () {
                $interval = $this->config->get('config_center.drivers.apollo.interval', 5);
                retry(INF, function () {
                    while (true) {
                        $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                        $workerExited = $coordinator->yield($this->getInterval());
                        if ($workerExited) {
                            break;
                        }
                        $this->pull();
                    }
                }, $interval * 1000);
            });
        } else {
            $this->configFetcherHandle();
        }
    }

    protected function pull(): array
    {
        [$namespaces, $callbacks] = $this->createNamespaceCallbacks();
        $this->client->pull($namespaces, $callbacks);
    }

    protected function createNamespaceCallbacks(): array
    {
        return $this->buildNamespacesCallbacks(function ($configs, $namespace) {
            if (isset($configs['configurations'], $configs['releaseKey'])) {
                $configs['namespace'] = $namespace;
                $pipeMessage = $this->pipeMessage;
                $data = new $pipeMessage($configs);

                $option = $this->client->getOption();
                $cacheKey = $option->buildCacheKey($data->namespace);
                $cachedKey = ReleaseKey::get($cacheKey);
                if ($cachedKey && $cachedKey === $data->releaseKey) {
                    return;
                }
                $this->updateConfig($data->configurations);
                ReleaseKey::set($cacheKey, $data->releaseKey);
            }
        });
    }


    public function onPipeMessage(object $event): void
    {
        if (property_exists($event, 'data') && $event->data instanceof PipeMessage) {
            /** @var PipeMessage $data */
            $data = $event->data;

            if (! $data->isValid()) {
                return;
            }

            $option = $this->client->getOption();
            $cacheKey = $option->buildCacheKey($data->namespace);
            $cachedKey = ReleaseKey::get($cacheKey);
            if ($cachedKey && $cachedKey === $data->releaseKey) {
                return;
            }
            $this->updateConfig($data->configurations);
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

    protected function updateConfig(array $config)
    {
        foreach ($config ?? [] as $key => $value) {
            $this->config->set($key, $this->formatValue($value));
            $this->logger->debug(sprintf('Config [%s] is updated', $key));
        }
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
