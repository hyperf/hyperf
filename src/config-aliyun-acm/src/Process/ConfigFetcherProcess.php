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
namespace Hyperf\ConfigAliyunAcm\Process;

use Hyperf\ConfigAliyunAcm\ClientInterface;
use Hyperf\ConfigAliyunAcm\PipeMessage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessCollector;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class ConfigFetcherProcess extends AbstractProcess
{
    public $name = 'aliyun-acm-config-fetcher';

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
     * @var array
     */
    private $cacheConfig;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
        $this->config = $container->get(ConfigInterface::class);
    }

    public function bind(Server $server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    public function isEnable(): bool
    {
        return $this->config->get('aliyun_acm.enable', false)
            && $this->config->get('aliyun_acm.use_standalone_process', true);
    }

    public function handle(): void
    {
        while (true) {
            $config = $this->client->pull();
            if ($config !== $this->cacheConfig) {
                $this->cacheConfig = $config;
                $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
                $pipeMessage = new PipeMessage($config);
                for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                    $this->server->sendMessage($pipeMessage, $workerId);
                }

                $processes = ProcessCollector::all();
                if ($processes) {
                    $string = serialize($pipeMessage);
                    /** @var \Swoole\Process $process */
                    foreach ($processes as $process) {
                        $process->exportSocket()->send($string);
                    }
                }
            }

            sleep($this->config->get('aliyun_acm.interval', 5));
        }
    }
}
