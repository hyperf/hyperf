<?php

namespace Hyperf\ConfigApollo\Process;


use Hyperf\ConfigApollo\ClientInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\Process;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class ConfigFetcherProcess extends Process
{

    public $name = 'config-fetcher';

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

    public function handle(): void
    {
        $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
        $ipcCallback = function ($configs) use ($workerCount) {
            if (isset($configs['configurations'], $configs['releaseKey'])) {
                for ($i = 0; $i <= $workerCount; $i++) {
                    $this->server->sendMessage($configs, $i);
                }
            }
        };
        while (true) {
            $callbacks = [];
            $namespaces = $this->config->get('config-center.apollo.namespaces', []);
            foreach ($namespaces as $namespace) {
                $callbacks[$namespace] = $ipcCallback;
            }
            $this->client->pull($namespaces, $callbacks);
            sleep($this->config->get('config-center.apollo.interval', 5));
        }
    }

}