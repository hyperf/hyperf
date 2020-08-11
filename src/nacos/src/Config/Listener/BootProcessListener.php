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
namespace Hyperf\Nacos\Config\Listener;

use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Nacos\Config\Client;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class BootProcessListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->config = $container->get(ConfigInterface::class);
        $this->client = $this->container->get(Client::class);
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
        if (! $this->config->get('nacos.config.enable', false)) {
            return;
        }

        foreach ($this->client->pull() as $key => $conf) {
            $this->config->set($key, $conf);
        }

        if (! $this->config->get('nacos.config.use_standalone_process', true)) {
            Coroutine::create(function () {
                $interval = $this->config->get('nacos.config.interval', 3);
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
                            foreach ($config as $key => $conf) {
                                $this->config->set($key, $conf);
                            }
                        }
                        $prevConfig = $config;
                    }
                }, $interval * 1000);
            });
        }
    }
}
