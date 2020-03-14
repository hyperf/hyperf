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

namespace Hyperf\ConfigAliyunAcm\Listener;

use Hyperf\ConfigAliyunAcm\ClientInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class BootProcessListener implements ListenerInterface
{
    /**
     * @var ConfigInterface
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

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->client = $container->get(ClientInterface::class);
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
        if (! $this->config->get('aliyun_acm.enable', false)) {
            return;
        }

        if ($config = $this->client->pull()) {
            $this->updateConfig($config);
        }

        if (! $this->config->get('aliyun_acm.use_standalone_process', true)) {
            Coroutine::create(function () {
                $interval = $this->config->get('aliyun_acm.interval', 5);
                retry(INF, function () use ($interval) {
                    while (true) {
                        sleep($interval);
                        $config = $this->client->pull();
                        if ($config !== $this->config) {
                            $this->updateConfig($config);
                        }
                    }
                }, $interval * 1000);
            });
        }
    }

    protected function updateConfig(array $config){
        foreach ($config as $key => $value) {
            if (is_string($key)) {
                $this->config->set($key, $value);
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
        }
    }
}
