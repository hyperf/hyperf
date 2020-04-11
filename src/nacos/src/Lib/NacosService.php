<?php

namespace Hyperf\Nacos\Lib;

use Hyperf\Nacos\Model\ServiceModel;

class NacosService extends AbstractNacos
{
    public function create(ServiceModel $serviceModel)
    {
        return $this->request('POST', "/nacos/v1/ns/service?{$serviceModel}") == 'ok';
    }

    public function delete(ServiceModel $serviceModel)
    {
        return $this->request('DELETE', "/nacos/v1/ns/service?{$serviceModel}") == 'ok';
    }

    public function update(ServiceModel $serviceModel)
    {
        return $this->request('PUT', "/nacos/v1/ns/service?{$serviceModel}") == 'ok';
    }

    public function detail(ServiceModel $serviceModel)
    {
        return $this->request('GET', "/nacos/v1/ns/service?{$serviceModel}");
    }

    public function list($pageNo, $pageSize = 10, $groupName = null, $namespaceId = null)
    {
        $params = array_filter(compact('pageNo', 'pageSize', 'groupName', 'namespaceId'));
        $params_str = http_build_query($params);

        return $this->request('GET', "/nacos/v1/ns/service/list?{$params_str}");
    }
}
