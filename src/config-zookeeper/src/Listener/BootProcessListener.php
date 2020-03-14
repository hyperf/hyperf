<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ConfigZookeeper\Listener;

use Hyperf\ConfigZookeeper\ClientInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Utils\Coroutine;

class BootProcessListener implements ListenerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ConfigInterface $config, StdoutLoggerInterface $logger, ClientInterface $client)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->client = $client;
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
            BeforeProcessHandle::class,
        ];
    }

    public function process(object $event)
    {
        if (! $this->config->get('zookeeper.enable', false)) {
            return;
        }

        if (! $this->config->get('zookeeper.use_standalone_process', true)) {
            Coroutine::create(function () {
                $interval = $this->config->get('zookeeper.interval', 5);
                retry(INF, function () use ($interval) {
                    while (true) {
                        $config = $this->client->pull();
                        if ($config !== $this->config) {
                            foreach ($config ?? [] as $key => $value) {
                                $this->config->set($key, $value);
                                $this->logger->debug(sprintf('Config [%s] is updated', $key));
                            }
                        }
                        sleep($interval);
                    }
                }, $interval * 1000);
            });
        }
    }
}
