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
use Hyperf\Utils\Codec\Json;

class NacosAuth extends AbstractNacos
{
    public function login(string $username, string $password): array
    {
        $response = $this->client()->request('POST', '/nacos/v1/auth/users/login', [
            RequestOptions::QUERY => [
                'username' => $username,
                'password' => $password,
            ],
        ]);
        return Json::decode($response->getBody()->getContents());
    }
}
