<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DistributedLock\Driver;

use Hyperf\Consul\KVInterface;
use Hyperf\Consul\SessionInterface;
use Hyperf\DistributedLock\Mutex;
use Psr\Container\ContainerInterface;

class ConsulDriver extends Driver
{
    /**
     * @var mixed|SessionInterface
     */
    protected $session;

    /**
     * @var KVInterface|mixed
     */
    protected $kv;

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);
        $this->retry = $config['retry'] ?? 0;
        $this->retryDelay = $config['retry_delay'] ?? 200;
        $this->driftFactor = $config['drift_factor'] ?? 0.01;

        $this->session = $this->container->get(SessionInterface::class);
        $this->kv = $this->container->get(KVInterface::class);
    }

    /**
     * @param string $resource
     * @param int $ttl
     * @return Mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function lock(string $resource, int $ttl): Mutex
    {
        $mutex = new Mutex();
        // Start a session
        $sessionId = $this->session->create()->json()['ID'];

        $token = '';

        // Lock a key / value with the current session
        $lockAcquired = $this->kv->put($resource, $token, ['acquire' => $sessionId])->json();
        if ($lockAcquired === false) {
            $this->session->destroy($sessionId);

            return $mutex;
        }

        return $mutex->setIsAcquired()
            ->setContext([
                'session_id' => $sessionId,
                'resource' => $resource,
                'token' => $token,
            ]);
    }

    /**
     * @param Mutex $mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function unlock(Mutex $mutex): void
    {
        $context = $mutex->getContext();
        $sessionId = $context['session_id'] ?? '';
        $resource = $context['resource'] ?? '';

        $this->kv->delete($resource);
        $this->session->destroy($sessionId);
    }
}
