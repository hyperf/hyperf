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
use Hyperf\Nacos\Model\ConfigModel;
use Hyperf\Utils\Codec\Json;

class NacosConfig extends AbstractNacos
{
    public function get(ConfigModel $configModel): array
    {
        $response = $this->request('GET', '/nacos/v1/cs/configs', [
            RequestOptions::QUERY => $configModel->toArray(),
        ]);

        $statusCode = $response->getStatusCode();
        $contents = (string) $response->getBody();
        if ($statusCode !== 200) {
            return [];
        }

        return $configModel->parse($contents);
    }

    public function set(ConfigModel $configModel): array
    {
        $response = $this->request('POST', '/nacos/v1/cs/configs', [
            RequestOptions::FORM_PARAMS => $configModel->toArray(),
        ]);

        return Json::decode((string) $response->getBody());
    }

    public function delete(ConfigModel $configModel): array
    {
        $response = $this->request('DELETE', '/nacos/v1/cs/configs', [
            RequestOptions::QUERY => $configModel->toArray(),
        ]);

        return Json::decode((string) $response->getBody());
    }
}
