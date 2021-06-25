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
namespace Hyperf\ConfigCenter;

use Hyperf\ConfigCenter\Contract\ClientInterface;
use Hyperf\ConfigCenter\Contract\DriverInterface;
use Hyperf\ConfigCenter\Contract\PipeMessageInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Channel;
use Hyperf\Process\ProcessCollector;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Swoole\Server;

abstract class AbstractDriver implements DriverInterface
{
    /**
     * @var null|Server
     */
    protected $server;

    /**
     * @var null|ConfigInterface
     */
    protected $config;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var null|string
     */
    protected $pipeMessage = PipeMessage::class;

    /**
     * @var string
     */
    protected $driverName = '';

    /**
     * @var \Hyperf\Engine\Channel
     */
    protected $channel;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->channel = new Channel();
    }

    public function createConfigUpdaterLoop(string $mode): void
    {
        Coroutine::create(function () use ($mode) {
            retry(INF, function () use ($mode) {
                $prevConfig = [];
                $interval = $this->getInterval();
                while (true) {
                    try {
                        $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                        $workerExited = $coordinator->yield($interval);
                        if ($workerExited) {
                            break;
                        }
                        $config = $this->channel->pop();
                        if ($config !== $prevConfig) {
                            $prevConfig = $config;
                            if ($mode === Mode::PROCESS) {
                                $this->shareConfigToProcesses($config);
                            } else {
                                $this->updateConfig($config);
                            }
                        }
                    } catch (\Throwable $exception) {
                        $this->logger->error((string) $exception);
                        throw $exception;
                    }
                }
            });
        });
    }

    public function createConfigFetcherLoop(): void
    {
        Coroutine::create(function () {
            $interval = $this->getInterval();
            retry(INF, function () use ($interval) {
                while (true) {
                    try {
                        $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                        $workerExited = $coordinator->yield($interval);
                        if ($workerExited) {
                            break;
                        }
                        $this->channel->push($this->pull());
                    } catch (\Throwable $exception) {
                        $this->logger->error((string) $exception);
                        throw $exception;
                    }
                }
            }, $interval * 1000);
        });
    }

    public function fetchConfig()
    {
        if (method_exists($this->client, 'pull')) {
            $config = $this->pull();
            $config && is_array($config) && $this->channel->push($config);
        }
    }

    public function onPipeMessage(PipeMessageInterface $pipeMessage): void
    {
        $this->updateConfig($pipeMessage->getData());
    }

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setServer($server): AbstractDriver
    {
        $this->server = $server;
        return $this;
    }

    protected function pull(): array
    {
        return $this->client->pull();
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

    protected function getInterval(): int
    {
        return (int) $this->config->get('config_center.drivers.' . $this->driverName . '.interval', 5);
    }

    protected function shareConfigToProcesses(array $config): void
    {
        $pipeMessage = $this->pipeMessage;
        $message = new $pipeMessage($config);
        if (! $message instanceof PipeMessageInterface) {
            throw new \InvalidArgumentException('Invalid pipe message object.');
        }
        $this->shareMessageToWorkers($message);
        $this->shareMessageToUserProcesses($message);
    }

    protected function shareMessageToWorkers(PipeMessageInterface $message): void
    {
        if ($this->server instanceof Server) {
            $workerCount = $this->server->setting['worker_num'] + ($this->server->setting['task_worker_num'] ?? 0) - 1;
            for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                $this->server->sendMessage($message, $workerId);
            }
        }
    }

    protected function shareMessageToUserProcesses(PipeMessageInterface $message): void
    {
        $processes = ProcessCollector::all();
        if ($processes) {
            $string = serialize($message);
            /** @var \Swoole\Process $process */
            foreach ($processes as $process) {
                $result = $process->exportSocket()->send($string, 10);
                if ($result === false) {
                    $this->logger->error('Configuration synchronization failed. Please restart the server.');
                }
            }
        }
    }
}
