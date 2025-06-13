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

namespace Hyperf\ConfigZookeeper;

use Hyperf\Contract\ConfigInterface;
use Swoole\Zookeeper;

class Client implements ClientInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    public function pull(): array
    {
        $zk = new Zookeeper($this->config->get('config_center.drivers.zookeeper.server'), 2.5);
        $path = $this->config->get('config_center.drivers.zookeeper.path', '/conf');
        $config = $zk->get($path);
        return json_decode($config, true);
    }
}
