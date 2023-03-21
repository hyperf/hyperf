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

namespace Hyperf\GrpcClient;

use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcClient\Exception\GrpcClientConnectFailedException;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\Utils\Exception\ExceptionThrower;
use RuntimeException;
use Throwable;
use Psr\Container\ContainerInterface;

class GrpcTransporter implements TransporterInterface
{

    /**
     * If $loadBalancer is null, will select a node in $nodes to request,
     * otherwise, use the nodes in $loadBalancer.
     *
     * @var Node[]
     */
    private array $nodes = [];
    const CONFIG_DEFAULT_CONNECT_TIMEOUT = 3;
    const CONFIG_DEFAULT_RECV_TIMEOUT = 5.0;
    const CONFIG_DEFAULT_RETRY_COUNT = 2;
    const CONFIG_DEFAULT_RETRY_INTERVAL = 0;
    const CONFIG_DEFAULT_CLIENT_COUNT = 4;

    protected array $config = [
        'connect_timeout' => self::CONFIG_DEFAULT_CONNECT_TIMEOUT,
        'recv_timeout' => self::CONFIG_DEFAULT_RECV_TIMEOUT,
        'retry_count' => self::CONFIG_DEFAULT_RETRY_COUNT,
        'retry_interval' => self::CONFIG_DEFAULT_RETRY_INTERVAL,
        'client_count' => self::CONFIG_DEFAULT_CLIENT_COUNT,
    ];

    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);
        $this->factory = make(ClientFactory::class, [
            'config' => [
                'connect_timeout' => $this->config['connect_timeout'] ?? self::CONFIG_DEFAULT_CONNECT_TIMEOUT,
                'read_timeout' => $this->config['recv_timeout'] ?? self::CONFIG_DEFAULT_RECV_TIMEOUT,
                'client_count' => $this->config['client_count'] ?? self::CONFIG_DEFAULT_CLIENT_COUNT,
            ]
        ]);
    }


    public function send(string $data)
    {
        $unserializeData = unserialize($data);
        $method = $unserializeData['method'] ?? '';
        $id = $unserializeData['id'] ?? '';
        $params = $unserializeData['params'][0] ?? [];

        $retryCount = $this->config['retry_count'] ?? self::CONFIG_DEFAULT_RETRY_COUNT;
        $retryInterval = $this->config['retry_interval'] ?? self::CONFIG_DEFAULT_RETRY_INTERVAL;

        $response = retry($retryCount, function () use ($method, $params) {
            try {
                $client = $this->factory->get();
                $request = new Request($method, $params, []);
                $streamId = $client->send($request);
                $response = $client->recv($streamId, $this->config['recv_timeout'] ?? self::CONFIG_DEFAULT_RECV_TIMEOUT);
                if ($response == false) {
                    throw new GrpcClientException("grpc request error ");
                }
                return $response;
            } catch (Throwable $exception) {
                if ($this->shouldBeRetry($exception)) {
                    throw $exception;
                }
                return new ExceptionThrower($exception);
            }

        }, $retryInterval);

        if ($response instanceof ExceptionThrower) {
            throw $response->getThrowable();
        }

        if ($response->headers['grpc-status'] == StatusCode::OK) {
            $responseData = ['id' => $id, 'result' => $response->data];
        } else {
            $responseData = [
                'id' => $id,
                'error' => [
                    'code' => intval($response->headers['grpc-status']),
                    'message' => $response->headers['grpc-message'],
                ],
            ];
        }
        return serialize($responseData);
    }

    public function recv()
    {
        throw new RuntimeException(__CLASS__ . ' does not support recv method.');
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
        return $throwable instanceof GrpcClientConnectFailedException;
    }
}
