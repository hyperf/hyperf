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
use Psr\Container\ContainerInterface;
use Swoole\Zookeeper;

class Client implements ClientInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
    }

    public function pull(): array
    {
        $zk = new Zookeeper($this->config->get('zookeeper.server'), 2.5);
        $path = $this->config->get('zookeeper.path', '/conf');
        $config = $zk->get($path);
        return json_decode($config, true);
    }
}
