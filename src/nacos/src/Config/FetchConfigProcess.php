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
namespace Hyperf\Nacos\Config;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Client;
use Hyperf\Process\AbstractProcess;
use Swoole\Coroutine\Server as CoServer;
use Swoole\Server;

class FetchConfigProcess extends AbstractProcess
{
    public $name = 'nacos-fetch-config';

    /**
     * @var CoServer|Server
     */
    private $server;

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
        while (true) {
            $remote_config = $client->pull();
            if ($remote_config != $cache) {
                $pipe_message = new PipeMessage($remote_config);
                for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                    $this->server->sendMessage($pipe_message, $workerId);
                }
                $cache = $remote_config;
            }
            sleep((int) $config->get('nacos.config_reload_interval', 3));
        }
    }

    public function isEnable($server): bool
    {
        $config = $this->container->get(ConfigInterface::class);
        return $server instanceof Server && (bool) $config->get('nacos.config_reload_interval', false);
    }
}
