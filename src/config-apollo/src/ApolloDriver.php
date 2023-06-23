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

use Hyperf\ConfigApollo\ClientInterface as ApolloClientInterface;
use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\ConfigCenter\Contract\ClientInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function Hyperf\Support\retry;

class ApolloDriver extends AbstractDriver
{
    protected string $driverName = 'apollo';

    protected array $notifications = [];

    /**
     * @var ApolloClientInterface
     */
    protected ClientInterface $client;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ApolloClientInterface::class);
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
                $this->syncConfig($config, $prevConfig);
                $prevConfig = $config;
            }
        });
    }

    protected function handleLongPullingLoop(): void
    {
        $prevConfig = [];
        $channel = new Channel(1);
        $this->longPulling($channel);
        Coroutine::create(function () use (&$prevConfig, $channel) {
            while (true) {
                try {
                    $namespaces = $channel->pop();
                    if (! $namespaces && $channel->isClosing()) {
                        break;
                    }
                    $config = $this->client->parallelPull($namespaces);
                    if ($config !== $prevConfig) {
                        $this->syncConfig($config, $prevConfig);
                        $prevConfig = $config;
                    }
                } catch (Throwable $exception) {
                    $this->logger->error((string) $exception);
                }
            }
        });
    }

    protected function loop(callable $callable, ?Channel $channel = null): int
    {
        return Coroutine::create(function () use ($callable, $channel) {
            $interval = $this->getInterval();
            retry(INF, function () use ($callable, $channel, $interval) {
                while (true) {
                    try {
                        $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                        $untilEvent = $coordinator->yield($interval);
                        if ($untilEvent) {
                            $channel && $channel->close();
                            break;
                        }
                        $callable();
                    } catch (Throwable $exception) {
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
        }, $channel);
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

    protected function updateConfig(array $config): void
    {
        $mergedConfigs = [];
        foreach ($config as $c) {
            foreach ($c as $key => $value) {
                $mergedConfigs[$key] = $value;
            }
        }
        unset($config);
        foreach ($mergedConfigs as $key => $value) {
            $this->config->set($key, $this->formatValue($value));
            $this->logger->debug(sprintf('Config [%s] is updated', $key));
        }
    }
}
