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

use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Utils\Codec\Json;

class NacosService extends AbstractNacos
{
    public function create(ServiceModel $serviceModel): bool
    {
        $response = $this->request('POST', "/nacos/v1/ns/service?{$serviceModel}");

        return $response->getBody()->getContents() == 'ok';
    }

    public function delete(ServiceModel $serviceModel): bool
    {
        $response = $this->request('DELETE', "/nacos/v1/ns/service?{$serviceModel}");

        return $response->getBody()->getContents() == 'ok';
    }

    public function update(ServiceModel $serviceModel): bool
    {
        $response = $this->request('PUT', "/nacos/v1/ns/service?{$serviceModel}");

        return $response->getBody()->getContents() == 'ok';
    }

    public function detail(ServiceModel $serviceModel): array
    {
        $response = $this->request('GET', "/nacos/v1/ns/service?{$serviceModel}");

        return Json::decode($response->getBody()->getContents());
    }

    public function list($pageNo, $pageSize = 10, $groupName = null, $namespaceId = null): array
    {
        $params = array_filter(compact('pageNo', 'pageSize', 'groupName', 'namespaceId'));
        $params_str = http_build_query($params);

        $response = $this->request('GET', "/nacos/v1/ns/service/list?{$params_str}");

        return Json::decode($response->getBody()->getContents());
    }
}
