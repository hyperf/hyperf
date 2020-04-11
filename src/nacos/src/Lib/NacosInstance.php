<?php

namespace Hyperf\Nacos\Lib;

use Hyperf\LoadBalancer\Node;
use Hyperf\LoadBalancer\Random;
use Hyperf\LoadBalancer\RoundRobin;
use Hyperf\LoadBalancer\WeightedRandom;
use Hyperf\LoadBalancer\WeightedRoundRobin;
use Hyperf\Nacos\Model\InstanceModel;
use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Utils\Arr;

class NacosInstance extends AbstractNacos
{
    public function register(InstanceModel $instanceModel)
    {
        return $this->request('POST', "/nacos/v1/ns/instance?{$instanceModel}") == 'ok';
    }

    public function delete(InstanceModel $instanceModel)
    {
        return $this->request('DELETE', "/nacos/v1/ns/instance?{$instanceModel}") == 'ok';
    }

    public function update(InstanceModel $instanceModel)
    {
        $instanceModel->healthy = null;

        return $this->request('PUT', "/nacos/v1/ns/instance?{$instanceModel}") == 'ok';
    }

    public function list(ServiceModel $serviceModel, array $clusters = [], $healthyOnly = null)
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

        return $this->request('GET', "/nacos/v1/ns/instance/list?{$params_str}");
    }

    public function getOptimal(ServiceModel $serviceModel, array $clusters = [])
    {
        $list = $this->list($serviceModel, $clusters, true);
        $instance = $list['hosts'] ?? [];
        if (!$instance) {
            return false;
        }
        $enabled = array_filter($instance, function ($item) {
            return $item['enabled'];
        });

        $tactics = strtolower(config('nacos.loadBalancer', 'random'));

        return $this->loadBalancer($enabled, $tactics);
    }

    protected function loadBalancer($nodes, $tactics = 'random')
    {
        $load_nodes = [];
        $nacos_nodes = [];
        /** @var InstanceModel $node */
        foreach ($nodes as $node) {
            $load_nodes[] = new Node($node['ip'], $node['port'], $node['weight']);
            $nacos_nodes["{$node['ip']}:{$node['port']}"] = $node;
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

    public function detail(InstanceModel $instanceModel)
    {
        return $this->request('GET', "/nacos/v1/ns/instance?{$instanceModel}");
    }

    public function beat(ServiceModel $serviceModel, InstanceModel $instanceModel)
    {
        $serviceName = $serviceModel->serviceName;
        $groupName = $serviceModel->groupName;
        $ephemeral = $instanceModel->ephemeral;
        $params = array_filter(compact('serviceName', 'beat', 'groupName', 'ephemeral'), function ($item) {
            return $item !== null;
        });
        $params['beat'] = $instanceModel->toJson();
        $params_str = http_build_query($params);

        return $this->request('PUT', "/nacos/v1/ns/instance/beat?{$params_str}");
    }

    public function upHealth(InstanceModel $instanceModel)
    {
        if ($instanceModel->healthy === null) {
            $instanceModel->healthy = true;
        }

        return $this->request('PUT', "/nacos/v1/ns/health/instance?{$instanceModel}") == 'ok';
    }
}
