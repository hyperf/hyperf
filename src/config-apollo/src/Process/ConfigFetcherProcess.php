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
namespace Hyperf\ConfigApollo\Process;

use Hyperf\ConfigApollo\ClientInterface;
use Hyperf\ConfigApollo\PipeMessage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessCollector;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class ConfigFetcherProcess extends AbstractProcess
{
    public $name = 'apollo-config-fetcher';

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
        return $this->config->get('apollo.enable', false)
            && $this->config->get('apollo.use_standalone_process', true);
    }

    public function handle(): void
    {
        $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
        $ipcCallback = function ($configs, $namespace) use ($workerCount) {
            if (isset($configs['configurations'], $configs['releaseKey'])) {
                $configs['namespace'] = $namespace;
                $pipeMessage = new PipeMessage($configs);
                for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                    $this->server->sendMessage($pipeMessage, $workerId);
                }

                $string = serialize($pipeMessage);

                $processes = ProcessCollector::all();
                /** @var \Swoole\Process $process */
                foreach ($processes as $process) {
                    $process->exportSocket()->send($string);
                }
            }
        };
        while (true) {
            $callbacks = [];
            $namespaces = $this->config->get('apollo.namespaces', []);
            foreach ($namespaces as $namespace) {
                if (is_string($namespace)) {
                    $callbacks[$namespace] = $ipcCallback;
                }
            }
            $this->client->pull($namespaces, $callbacks);
            sleep($this->config->get('apollo.interval', 5));
        }
    }
}
