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

use Hyperf\Consul\KV;
use Hyperf\Consul\KVInterface;
use Hyperf\Consul\Session;
use Hyperf\Consul\SessionInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DistributedLock\Mutex;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;

class ConsulDriver extends Driver
{
    /**
     * @var int
     */
    protected $retryDelay;

    /**
     * @var int
     */
    protected $retry;

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

        $this->session = $this->createSession();
        $this->kv = $this->createKV();
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
        $token = $sessionId = $this->session->create([
            'LockDelay' => '0.5s',
            'Behavior' => 'release',
            'TTL' => $ttl . 's',
        ])->json()['ID'];

        $retry = $this->retry;
        do {
            // Lock a key / value with the current session
            $lockAcquired = $this->kv->put($resource, $token, ['acquire' => $sessionId])->json();
            if ($lockAcquired === false) {
                // Wait a random delay before to retry
                $delay = mt_rand((int) floor($this->retryDelay / 2), $this->retryDelay);
                usleep($delay * 1000);
            }
            --$retry;
        } while ($lockAcquired === false && $retry > 0);

        if ($lockAcquired === false) {
            $this->session->destroy($sessionId);

            return $mutex;
        }

        return $mutex->setAcquired()
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

    /**
     * @return Session
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    protected function createSession()
    {
        return new Session(function () {
            return $this->container->get(ClientFactory::class)->create([
                'base_uri' => $this->container->get(ConfigInterface::class)->get('consul.uri', Session::DEFAULT_URI),
            ]);
        }, $this->container->get(LoggerFactory::class)->get('default'));
    }

    /**
     * @return KV
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    protected function createKV()
    {
        return new KV(function () {
            return $this->container->get(ClientFactory::class)->create([
                'base_uri' => $this->container->get(ConfigInterface::class)->get('consul.uri', KV::DEFAULT_URI),
            ]);
        }, $this->container->get(LoggerFactory::class)->get('default'));
    }
}
