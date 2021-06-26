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
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Coroutine\Channel;

class ApolloDriver extends AbstractDriver
{
    /**
     * @var ClientInterface
     */
    protected $client;

    protected $driverName = 'apollo';

    protected $notifications = [];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
    }

    public function createMessageFetcherLoop(): void
    {
        $pullMode = $this->config->get('config_center.drivers.apollo.pull_mode', PullMode::INTERVAL);
        if ($pullMode === PullMode::LONG_PULLING) {
            $this->handleLongPullingLoop();
        } elseif ($pullMode === PullMode::INTERVAL) {
            $this->handleIntervalLoop();
        }
    }

    protected function handleIntervalLoop(): void
    {
        $prevConfig = [];
        $this->loop(function () use (&$prevConfig) {
            $config = $this->pull();
            if ($config !== $prevConfig) {
                $this->syncConfig($config);
                $prevConfig = $config;
            }
        });
    }

    protected function handleLongPullingLoop(): void
    {
        $prevConfig = [];
        $channel = new Channel();
        $this->longPulling($channel);
        $this->loop(function () use (&$prevConfig, $channel) {
            $namespaces = $channel->pop();
            $config = $this->client->parallelPull($namespaces);
            if ($config !== $prevConfig) {
                $this->syncConfig($config);
                $prevConfig = $config;
            }
        });
    }

    protected function loop(callable $callable, string $until = Constants::WORKER_EXIT, ?int $interval = null): int
    {
        if (is_null($interval)) {
            $interval = $this->getInterval();
        }
        return Coroutine::create(function () use ($callable, $until, $interval) {
            retry(INF, function () use ($callable, $until, $interval) {
                while (true) {
                    try {
                        $coordinator = CoordinatorManager::until($until);
                        $untilEvent = $coordinator->yield($interval);
                        if ($untilEvent) {
                            break;
                        }
                        $callable();
                    } catch (\Throwable $exception) {
                        $this->logger->error((string) $exception);
                        throw $exception;
                    }
                }
            }, $interval * 1000);
        });
    }

    protected function longPulling(Channel $channel): void
    {
        $namespaces = $this->config->get('config_center.drivers.apollo.namespaces', []);
        foreach ($namespaces as $namespace) {
            $this->notifications[$namespace] = [
                'namespaceName' => $namespace,
                'notificationId' => -1,
            ];
        }
        $this->loop(function () use ($channel) {
            $response = $this->client->longPulling($this->notifications);
            if ($response instanceof ResponseInterface && $response->getStatusCode() === 200) {
                $body = json_decode((string) $response->getBody(), true);
                foreach ($body as $item) {
                    if (isset($item['namespaceName'], $item['notificationId']) && $item['notificationId'] > $this->notifications[$item['namespaceName']]['notificationId']) {
                        $prevId = $this->notifications[$item['namespaceName']]['notificationId'];
                        $this->notifications[$item['namespaceName']]['notificationId'] = $afterId = $item['notificationId'];
                        $this->logger->debug(sprintf('Updated apollo namespace [%s] notification id from %s to %s', $item['namespaceName'], $prevId, $afterId));
                        if ($prevId > -1) {
                            $channel->push([$item['namespaceName']]);
                        }
                    }
                }
            }
        });
    }

    protected function pull(): array
    {
        return $this->client->pull();
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
        $mergedConfigs = [];
        foreach ($config as $c) {
            foreach ($c as $key => $value) {
                $mergedConfigs[$key] = $value;
            }
        }
        unset($config);
        foreach ($mergedConfigs ?? [] as $key => $value) {
            $this->config->set($key, $this->formatValue($value));
            $this->logger->debug(sprintf('Config [%s] is updated', $key));
        }
    }
}
