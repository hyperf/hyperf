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
    public function register(
        string $ip,
        int $port,
        string $serviceName,
        #[ArrayShape([
            'groupName' => '',
            'clusterName' => '',
            'namespaceId' => '',
            'weight' => 99.0,
            'metadata' => '',
            'enabled' => true,
            'ephemeral' => false, // 是否临时实例
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

    public function delete(
        string $serviceName,
        string $groupName,
        string $ip,
        int $port,
        #[ArrayShape([
            'clusterName' => '',
            'namespaceId' => '',
            'ephemeral' => false,
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

    public function update(
        string $ip,
        int $port,
        string $serviceName,
        #[ArrayShape([
            'groupName' => '',
            'clusterName' => '',
            'namespaceId' => '',
            'weight' => 0.99,
            'metadata' => '', // json
            'enabled' => false,
            'ephemeral' => false,
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

    public function list(
        string $serviceName,
        #[ArrayShape([
            'groupName' => '',
            'namespaceId' => '',
            'clusters' => '', // 集群名称(字
            'healthyOnly' => false,
        ])]
        array $optional = []
    ): ResponseInterface {
        return $this->request('GET', 'nacos/v1/ns/instance/list', [
            RequestOptions::QUERY => $this->filter(array_merge($optional, [
                'serviceName' => $serviceName,
            ])),
        ]);
    }

    public function detail(
        string $ip,
        int $port,
        string $serviceName,
        #[ArrayShape([
            'groupName' => '',
            'namespaceId' => '',
            'cluster' => '',
            'healthyOnly' => false,
            'ephemeral' => false,
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

    public function beat(
        string $serviceName,
        #[ArrayShape([
            'ip' => '',
            'port' => 9501,
            'serviceName' => '',
            'cluster' => '',
            'weight' => 1,
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

    public function updateHealth(
        string $ip,
        int $port,
        string $serviceName,
        bool $healthy,
        #[ArrayShape([
            'namespaceId' => '',
            'groupName' => '',
            'clusterName' => '',
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
