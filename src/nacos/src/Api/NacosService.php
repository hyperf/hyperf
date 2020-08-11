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
namespace Hyperf\Nacos\Api;

use GuzzleHttp\RequestOptions;
use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;

class NacosService extends AbstractNacos
{
    public function create(ServiceModel $serviceModel): bool
    {
        $response = $this->request('POST', '/nacos/v1/ns/service', [
            RequestOptions::QUERY => $serviceModel->toArray(),
        ]);

        return $response->getBody()->getContents() === 'ok';
    }

    public function delete(ServiceModel $serviceModel): bool
    {
        $response = $this->request('DELETE', '/nacos/v1/ns/service', [
            RequestOptions::QUERY => $serviceModel->toArray(),
        ]);

        return $response->getBody()->getContents() === 'ok';
    }

    public function update(ServiceModel $serviceModel): bool
    {
        $response = $this->request('PUT', '/nacos/v1/ns/service', [
            RequestOptions::QUERY => $serviceModel->toArray(),
        ]);

        return $response->getBody()->getContents() === 'ok';
    }

    public function detail(ServiceModel $serviceModel): array
    {
        $response = $this->request('GET', '/nacos/v1/ns/service', [
            RequestOptions::QUERY => $serviceModel->toArray(),
        ]);

        if (!$response instanceof ResponseInterface) {
            return [];
        }

        return Json::decode($response->getBody()->getContents());
    }

    public function list(int $pageNo, int $pageSize = 10, ?string $groupName = null, ?string $namespaceId = null): array
    {
        $params = array_filter(compact('pageNo', 'pageSize', 'groupName', 'namespaceId'));

        $response = $this->request('GET', '/nacos/v1/ns/service/list', [
            RequestOptions::QUERY => $params,
        ]);

        return Json::decode($response->getBody()->getContents());
    }
}
