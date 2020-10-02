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
namespace Hyperf\ConfigZookeeper\Process;

use Hyperf\ConfigZookeeper\ClientInterface;
use Hyperf\ConfigZookeeper\PipeMessage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class ConfigFetcherProcess extends AbstractProcess
{
    public $name = 'zookeeper-config-fetcher';

    // ext-swoole-zookeeper need use in coroutine
    public $enableCoroutine = true;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var string
     */
    private $cacheConfig;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
        $this->config = $container->get(ConfigInterface::class);
    }

    public function bind($server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    public function isEnable($server): bool
    {
        return $server instanceof Server
            && $this->config->get('zookeeper.enable', false)
            && $this->config->get('zookeeper.use_standalone_process', true);
    }

    public function handle(): void
    {
        while (true) {
            try {
                $config = $this->client->pull();
                if ($config !== $this->cacheConfig) {
                    $this->cacheConfig = $config;
                    $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
                    for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                        $this->server->sendMessage(new PipeMessage($config), $workerId);
                    }
                }
            } catch (\Throwable $exception) {
                if ($this->container->has(StdoutLoggerInterface::class) && $this->container->has(FormatterInterface::class)) {
                    $logger = $this->container->get(StdoutLoggerInterface::class);
                    $formatter = $this->container->get(FormatterInterface::class);
                    $logger->error($formatter->format($exception));
                }
            } finally {
                sleep($this->config->get('zookeeper.interval', 5));
            }
        }
    }
}
