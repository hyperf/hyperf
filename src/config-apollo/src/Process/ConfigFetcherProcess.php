<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ConfigApollo\Process;

use Hyperf\ConfigApollo\ClientInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Psr\Container\ContainerInterface;
use Swoole\Server;

/**
 * @Process(name="config-fetcher")
 */
class ConfigFetcherProcess extends AbstractProcess
{
    /**
     * @var string
     */
    public $name = 'config';

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
        return $this->config->get('config-center.enable', false);
    }

    public function handle(): void
    {
        $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
        $ipcCallback = function ($configs, $namespace) use ($workerCount) {
            if (isset($configs['configurations'], $configs['releaseKey'])) {
                $configs['namespace'] = $namespace;
                for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                    $this->server->sendMessage($configs, $workerId);
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
