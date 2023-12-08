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
use JetBrains\PhpStorm\ArrayShape;
use Psr\Http\Message\ResponseInterface;

class InstanceProvider extends AbstractProvider
{
    /**
     * @param array{groupName:string, clusterName:string, namespaceId:string, weight:float, metadata:string, enabled:bool, ephemeral:bool} $optional
     */
    public function register(
        string $ip,
        int $port,
        string $serviceName,
        #[ArrayShape([
            'groupName' => 'string',
            'clusterName' => 'string',
            'namespaceId' => 'string',
            'weight' => 'float',
            'metadata' => 'string',
            'enabled' => 'bool',
            'ephemeral' => 'bool',
        ])]
        array $optional = []
    ): ResponseInterface {
        return $this->request('POST', 'nacos/v1/ns/instance', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'serviceName' => $serviceName,
                'ip' => $ip,
                'port' => $port,
            ])),
        ]);
    }

    /**
     * @param array{clusterName:string, namespaceId:string, metadata:string, ephemeral:bool} $optional
     */
    public function delete(
        string $serviceName,
        string $groupName,
        string $ip,
        int $port,
        #[ArrayShape([
            'clusterName' => 'string',
            'namespaceId' => 'string',
            'metadata' => 'string',
            'ephemeral' => 'bool',
        ])]
        array $optional = []
    ): ResponseInterface {
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
     * @param array{groupName:string, clusterName:string, namespaceId:string, weight:float, metadata:string, enabled:bool, ephemeral:bool} $optional
     */
    public function update(
        string $ip,
        int $port,
        string $serviceName,
        #[ArrayShape([
            'groupName' => 'string',
            'clusterName' => 'string',
            'namespaceId' => 'string',
            'weight' => 'float',
            'metadata' => 'string',
            'enabled' => 'bool',
            'ephemeral' => 'bool',
        ])]
        array $optional = []
    ): ResponseInterface {
        return $this->request('PUT', 'nacos/v1/ns/instance', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'serviceName' => $serviceName,
                'ip' => $ip,
                'port' => $port,
            ])),
        ]);
    }

    /**
     * @param array{groupName:string, namespaceId:string, clusters:string, healthyOnly:bool} $optional
     *
     * optional.clusters 集群名称(字符串，多个集群用逗号分隔)
     */
    public function list(
        string $serviceName,
        #[ArrayShape(['groupName' => 'string',
            'namespaceId' => 'string',
            'clusters' => 'string',
            'healthyOnly' => 'bool',
        ])]
        array $optional = []
    ): ResponseInterface {
        return $this->request('GET', 'nacos/v1/ns/instance/list', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'serviceName' => $serviceName,
            ])),
        ]);
    }

    /**
     * @param array{groupName:string, namespaceId:string, clusters:string, healthyOnly:bool} $optional
     */
    public function detail(
        string $ip,
        int $port,
        string $serviceName,
        #[ArrayShape([
            'groupName' => 'string',
            'namespaceId' => 'string',
            'clusters' => 'string',
            'healthyOnly' => 'bool',
        ])]
        array $optional = []
    ): ResponseInterface {
        return $this->request('GET', 'nacos/v1/ns/instance', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'ip' => $ip,
                'port' => $port,
                'serviceName' => $serviceName,
            ])),
        ]);
    }

    /**
     * @param array{ip:?string, ?port:int, serviceName:string, cluster:string, weight:float} $beat
     */
    public function beat(
        string $serviceName,
        #[ArrayShape([
            'ip' => 'string',
            'port' => 'int',
            'serviceName' => 'string',
            'cluster' => 'string',
            'weight' => 'float',
        ])]
        array $beat = [],
        ?string $groupName = null,
        ?string $namespaceId = null,
        ?bool $ephemeral = null,
        bool $lightBeatEnabled = false
    ): ResponseInterface {
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
     * @param array{namespaceId:string, groupName:string, clusterName:string} $optional
     */
    public function updateHealth(
        string $ip,
        int $port,
        string $serviceName,
        bool $healthy,
        #[ArrayShape([
            'namespaceId' => 'string',
            'groupName' => 'string',
            'clusterName' => 'string',
        ])]
        array $optional = []
    ): ResponseInterface {
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
