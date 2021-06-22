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
namespace Hyperf\ConfigCenter;

use Hyperf\ConfigCenter\Contract\DriverInterface;
use Hyperf\Contract\ConfigInterface;

abstract class AbstractDriver implements DriverInterface
{
    protected $server;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function getServer()
    {
        return $this->server;
    }

    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }
}
