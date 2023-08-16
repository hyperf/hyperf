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
namespace Hyperf\Nacos\Provider;

use GuzzleHttp\RequestOptions;
use Hyperf\Codec\Json;
use Hyperf\Nacos\AbstractProvider;
use Psr\Http\Message\ResponseInterface;

class InstanceProvider extends AbstractProvider
{
    /**
     * @param $optional = [
     *     'groupName' => '',
     *     'clusterName' => '',
     *     'namespaceId' => '',
     *     'weight' => 99.0,
     *     'metadata' => '',
     *     'enabled' => true,
     *     'ephemeral' => false, // 是否临时实例
     * ]
     */
    public function register(string $ip, int $port, string $serviceName, array $optional = []): ResponseInterface
    {
        return $this->request('POST', 'nacos/v1/ns/instance', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'serviceName' => $serviceName,
                'ip' => $ip,
                'port' => $port,
            ])),
        ]);
    }

    /**
     * @param $optional = [
     *     'clusterName' => '',
     *     'namespaceId' => '',
     *     'ephemeral' => false,
     * ]
     */
    public function delete(string $serviceName, string $groupName, string $ip, int $port, array $optional = []): ResponseInterface
    {
        return $this->request('DELETE', 'nacos/v1/ns/instance', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'serviceName' => $serviceName,
                'groupName' => $groupName,
                'ip' => $ip,
                'port' => $port,
            ])),
        ]);
    }

    /**
     * @param $optional = [
     *     'groupName' => '',
     *     'clusterName' => '',
     *     'namespaceId' => '',
     *     'weight' => 0.99,
     *     'metadata' => '', // json
     *     'enabled' => false,
     *     'ephemeral' => false,
     * ]
     */
    public function update(string $ip, int $port, string $serviceName, array $optional = []): ResponseInterface
    {
        return $this->request('PUT', 'nacos/v1/ns/instance', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'serviceName' => $serviceName,
                'ip' => $ip,
                'port' => $port,
            ])),
        ]);
    }

    /**
     * @param $optional = [
     *     'groupName' => '',
     *     'namespaceId' => '',
     *     'clusters' => '', // 集群名称(字符串，多个集群用逗号分隔)
     *     'healthyOnly' => false,
     * ]
     */
    public function list(string $serviceName, array $optional = []): ResponseInterface
    {
        return $this->request('GET', 'nacos/v1/ns/instance/list', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'serviceName' => $serviceName,
            ])),
        ]);
    }

    /**
     * @param $optional = [
     *     'groupName' => '',
     *     'namespaceId' => '',
     *     'cluster' => '',
     *     'healthyOnly' => false,
     *     'ephemeral' => false,
     * ]
     */
    public function detail(string $ip, int $port, string $serviceName, array $optional = []): ResponseInterface
    {
        return $this->request('GET', 'nacos/v1/ns/instance', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'ip' => $ip,
                'port' => $port,
                'serviceName' => $serviceName,
            ])),
        ]);
    }

    /**
     * @param $beat = [
     *     'ip' => '',
     *     'port' => 9501,
     *     'serviceName' => '',
     *     'cluster' => '',
     *     'weight' => 1,
     * ]
     */
    public function beat(string $serviceName, array $beat = [], ?string $groupName = null, ?string $namespaceId = null, ?bool $ephemeral = null, bool $lightBeatEnabled = false): ResponseInterface
    {
        return $this->request('PUT', 'nacos/v1/ns/instance/beat', [
            RequestOptions::QUERY => $this->filter([
                'serviceName' => $serviceName,
                'ip' => $beat['ip'] ?? null,
                'port' => $beat['port'] ?? null,
                'groupName' => $groupName,
                'namespaceId' => $namespaceId,
                'ephemeral' => $ephemeral,
                'beat' => ! $lightBeatEnabled ? Json::encode($beat) : '',
            ]),
        ]);
    }

    /**
     * @param $optional = [
     *     'namespaceId' => '',
     *     'groupName' => '',
     *     'clusterName' => '',
     * ]
     */
    public function updateHealth(string $ip, int $port, string $serviceName, bool $healthy, array $optional = []): ResponseInterface
    {
        return $this->request('PUT', 'nacos/v1/ns/health/instance', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'ip' => $ip,
                'port' => $port,
                'serviceName' => $serviceName,
                'healthy' => $healthy,
            ])),
        ]);
    }
}
