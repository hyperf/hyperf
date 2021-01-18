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
use Hyperf\Nacos\Exception\RequestException;
use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Str;

class NacosService extends AbstractNacos
{
    public function create(ServiceModel $serviceModel): bool
    {
        $response = $this->request('POST', '/nacos/v1/ns/service', [
            RequestOptions::QUERY => $serviceModel->toArray(),
        ]);

        return (string) $response->getBody() === 'ok';
    }

    public function delete(ServiceModel $serviceModel): bool
    {
        $response = $this->request('DELETE', '/nacos/v1/ns/service', [
            RequestOptions::QUERY => $serviceModel->toArray(),
        ]);

        return (string) $response->getBody() === 'ok';
    }

    public function update(ServiceModel $serviceModel): bool
    {
        $response = $this->request('PUT', '/nacos/v1/ns/service', [
            RequestOptions::QUERY => $serviceModel->toArray(),
        ]);

        return (string) $response->getBody() === 'ok';
    }

    public function detail(ServiceModel $serviceModel): ?array
    {
        $response = $this->request('GET', '/nacos/v1/ns/service', [
            RequestOptions::QUERY => $serviceModel->toArray(),
        ]);

        $statusCode = $response->getStatusCode();
        $contents = (string) $response->getBody();
        if ($statusCode !== 200) {
            if (Str::contains($contents, 'is not found')) {
                return null;
            }

            throw new RequestException($contents, $statusCode);
        }

        return Json::decode($contents);
    }

    public function list(int $pageNo, int $pageSize = 10, ?string $groupName = null, ?string $namespaceId = null): array
    {
        $params = array_filter(compact('pageNo', 'pageSize', 'groupName', 'namespaceId'));

        $response = $this->request('GET', '/nacos/v1/ns/service/list', [
            RequestOptions::QUERY => $params,
        ]);

        return Json::decode((string) $response->getBody());
    }
}
