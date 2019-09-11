<?php
/**
 * ConsulDriver.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019-09-11 09:54
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\DistributedLock\Driver;


use Hyperf\Consul\KVInterface;
use Hyperf\Consul\SessionInterface;
use Psr\Container\ContainerInterface;

class ConsulDriver extends Driver
{
    /**
     * @var SessionInterface|mixed
     */
    protected $session;

    /**
     * @var KVInterface|mixed
     */
    protected $kv;

    public function __construct(ContainerInterface $container, array $config, string $prefix = 'lock/')
    {
        parent::__construct($container, $config, $prefix);
        $this->retry       = $config['retry'] ?? 0;
        $this->retryDelay  = $config['retry_delay'] ?? 200;
        $this->driftFactor = $config['drift_factor'] ?? 0.01;

        $this->session = $this->container->get(SessionInterface::class);
        $this->kv      = $this->container->get(KVInterface::class);
    }

    public function lock($resource, $ttl)
    {
        // Start a session
        $sessionId = $this->session->create()->json()['ID'];

        $token = '';

        // Lock a key / value with the current session
        return $lockAcquired = $this->kv->put($resource, $token, ['acquire' => $sessionId])->json();
    }

    public function unlock(array $lock)
    {
        $sessionId = $lock['sessionId'];
        $resourse  = $lock['resourse'];

        $this->kv->delete($resourse);
        $this->session->destroy($sessionId);
    }
}