<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Nacos\Lib;

use Hyperf\LoadBalancer\Node;
use Hyperf\LoadBalancer\Random;
use Hyperf\LoadBalancer\RoundRobin;
use Hyperf\LoadBalancer\WeightedRandom;
use Hyperf\LoadBalancer\WeightedRoundRobin;
use Hyperf\Nacos\Model\InstanceModel;
use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Utils\Codec\Json;

class NacosInstance extends AbstractNacos
{
    public function register(InstanceModel $instanceModel): bool
    {
        $response = $this->request('POST', "/nacos/v1/ns/instance?{$instanceModel}");

        return $response->getBody()->getContents() == 'ok';
    }

    public function delete(InstanceModel $instanceModel): bool
    {
        $response = $this->request('DELETE', "/nacos/v1/ns/instance?{$instanceModel}");

        return $response->getBody()->getContents() == 'ok';
    }

    public function update(InstanceModel $instanceModel): bool
    {
        $instanceModel->healthy = null;

        $response = $this->request('PUT', "/nacos/v1/ns/instance?{$instanceModel}");

        return $response->getBody()->getContents() == 'ok';
    }

    public function list(ServiceModel $serviceModel, array $clusters = [], $healthyOnly = null): array
    {
        $serviceName = $serviceModel->serviceName;
        $groupName = $serviceModel->groupName;
        $namespaceId = $serviceModel->namespaceId;
        $params = array_filter(compact('serviceName', 'groupName', 'namespaceId', 'clusters', 'healthyOnly'), function ($item) {
            return $item !== null;
        });
        if (isset($params['clusters'])) {
            $params['clusters'] = implode(',', $params['clusters']);
        }
        $params_str = http_build_query($params);

        $response = $this->request('GET', "/nacos/v1/ns/instance/list?{$params_str}");

        return Json::decode($response->getBody()->getContents());
    }

    public function getOptimal(ServiceModel $serviceModel, array $clusters = [])
    {
        $list = $this->list($serviceModel, $clusters, true);
        $instance = $list['hosts'] ?? [];
        if (! $instance) {
            return false;
        }
        $enabled = array_filter($instance, function ($item) {
            return $item['enabled'];
        });

        $tactics = strtolower(config('nacos.loadBalancer', 'random'));

        return $this->loadBalancer($enabled, $tactics);
    }

    public function detail(InstanceModel $instanceModel): array
    {
        $response = $this->request('GET', "/nacos/v1/ns/instance?{$instanceModel}");

        return Json::decode($response->getBody()->getContents());
    }

    public function beat(ServiceModel $serviceModel, InstanceModel $instanceModel): array
    {
        $serviceName = $serviceModel->serviceName;
        $groupName = $serviceModel->groupName;
        $ephemeral = $instanceModel->ephemeral;
        $params = array_filter(compact('serviceName', 'groupName', 'ephemeral'), function ($item) {
            return $item !== null;
        });
        $params['beat'] = $instanceModel->toJson();
        $params_str = http_build_query($params);

        $response = $this->request('PUT', "/nacos/v1/ns/instance/beat?{$params_str}");

        return Json::decode($response->getBody()->getContents());
    }

    public function upHealth(InstanceModel $instanceModel): bool
    {
        if ($instanceModel->healthy === null) {
            $instanceModel->healthy = true;
        }

        $response = $this->request('PUT', "/nacos/v1/ns/health/instance?{$instanceModel}");

        return $response == 'ok';
    }

    protected function loadBalancer($nodes, $tactics = 'random')
    {
        $load_nodes = [];
        $nacos_nodes = [];
        /** @var InstanceModel $node */
        foreach ($nodes as $node) {
            $load_nodes[] = new Node($node->ip, $node->port, $node->weight);
            $nacos_nodes["{$node->ip}:{$node->port}"] = $node;
        }

        switch ($tactics) {
            case 'roundrobin':
                $loader = new RoundRobin($load_nodes);
                break;
            case 'weightedrandom':
                $loader = new WeightedRandom($load_nodes);
                break;
            case 'weightedroundrobin':
                $loader = new WeightedRoundRobin($load_nodes);
                break;
            default:
                $loader = new Random($load_nodes);
                break;
        }

        /** @var Node $select */
        $select = $loader->select();

        return $nacos_nodes["{$select->host}:{$select->port}"];
    }
}
