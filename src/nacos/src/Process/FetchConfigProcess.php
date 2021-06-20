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
namespace Hyperf\Nacos\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Client;
use Hyperf\Nacos\Config\PipeMessage;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessCollector;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Server as CoServer;
use Swoole\Server;

class FetchConfigProcess extends AbstractProcess
{
    /**
     * @var string
     */
    public $name = 'nacos-config-fetcher';

    /**
     * @var CoServer|Server
     */
    protected $server;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function bind($server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    public function handle(): void
    {
        $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
        $cache = [];
        $config = $this->container->get(ConfigInterface::class);
        $client = $this->container->get(Client::class);
        while (ProcessManager::isRunning()) {
            $remoteConfig = $client->pull();
            if ($remoteConfig != $cache) {
                $pipeMessage = new PipeMessage($remoteConfig);
                for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                    $this->server->sendMessage($pipeMessage, $workerId);
                }

                $processes = ProcessCollector::all();
                if ($processes) {
                    $string = serialize($pipeMessage);
                    /** @var \Swoole\Process $process */
                    foreach ($processes as $process) {
                        $result = $process->exportSocket()->send($string, 10);
                        if ($result === false) {
                            $this->logger->error('Configuration synchronization failed. Please restart the server.');
                        }
                    }
                }

                $cache = $remoteConfig;
            }
            sleep((int) $config->get('nacos.config_reload_interval', 3));
        }
    }

    public function isEnable($server): bool
    {
        $config = $this->container->get(ConfigInterface::class);
        return $server instanceof Server
            && $config->get('nacos.config.enable', true)
            && $config->get('nacos.config.reload_interval', false);
    }
}
