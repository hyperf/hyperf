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
namespace Hyperf\RpcMultiplex;

use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\RpcMultiplex\Exception\NotSupportException;
use Hyperf\Support\Exception\ExceptionThrower;
use Multiplex\Exception\ChannelClosedException;
use Multiplex\Exception\ClientConnectFailedException;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Support\make;
use function Hyperf\Support\retry;

class Transporter implements TransporterInterface
{
    protected SocketFactory $factory;

    protected array $config = [
        'connect_timeout' => 5.0,
        'settings' => [
            'package_max_length' => 1024 * 1024 * 2,
        ],
        'recv_timeout' => 5.0,
        'retry_count' => 2,
        'retry_interval' => 0,
        'client_count' => 4,
        'heartbeat' => 20,
    ];

    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);
        $this->factory = make(SocketFactory::class, ['config' => $this->config]);
    }

    public function send(string $data)
    {
        $retryCount = $this->config['retry_count'] ?? 2;
        $retryInterval = $this->config['retry_interval'] ?? 0;
        $result = retry($retryCount, function () use ($data) {
            try {
                return $this->factory->get()->request($data);
            } catch (Throwable $exception) {
                if ($this->shouldBeRetry($exception)) {
                    throw $exception;
                }

                return new ExceptionThrower($exception);
            }
        }, $retryInterval);

        if ($result instanceof ExceptionThrower) {
            throw $result->getThrowable();
        }

        return $result;
    }

    public function recv()
    {
        throw new NotSupportException('Recv is not supported.');
    }

    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->factory->getLoadBalancer();
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        $this->factory->setLoadBalancer($loadBalancer);
        return $this;
    }

    protected function shouldBeRetry(Throwable $throwable): bool
    {
        return $throwable instanceof ClientConnectFailedException
            || $throwable instanceof ChannelClosedException;
    }
}
